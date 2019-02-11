<?php declare(strict_types=1);

namespace Stub;

use Stub\Exception\LambdaException;
use Stub\Token\TokenFilter;
use Stub\Token\TokenIterator;
use Stub\Token\Traverse\Criteria;

final class Tokenizer
{
    /**
     * @var string
     */
    const BRACKET_OPEN      = '{';

    /**
     * @var string
     */
    const BRACKET_CLOSE     = '}';

    /**
     * @var string
     */
    const LINE_BREAK        = "\n";

    /**
     * @var string
     */
    const PARENTHESIS_OPEN  = '(';

    /**
     * @var string
     */
    const PARENTHESIS_CLOSE = ')';

    /**
     * @var string
     */
    const SCOPE_RESOLUTION  = '::';

    /**
     * @var string
     */
    const SEMICOLON         = ';';

    /**
     * @var string
     */
    protected $source       = '';

    /**
     * @var TokenIterator $tokenIterator;
     */
    protected $tokenIterator;

    /**
     * @var mixed
     */
    protected $currentToken;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var bool
     */
    private $openTag        = false;

    /**
     * @var callable
     */
    private $logger;

    /**
     * @param  string $source
     * @return void
     */
    public function __construct(string $source, callable $logger)
    {
        $this->source  = $source;
        $this->logger  = $logger;
        $this->storage = new Storage();

        $this->initIterator();
    }

    /**
     * @return string
     */
    public function parse() : string
    {
        while ($this->getIterator()->valid()) {
            switch ($this->getIterator()->type()) {
                case T_OPEN_TAG:
                    $this->handleOpen();
                    break;
                case T_USE:
                    $this->handleUse();
                    break;
                case T_NAMESPACE:
                    $this->addEntity($this->initEntity(Storage::S_NAMESPACE));
                    break;
                case T_DOC_COMMENT:
                    $this->handleDocComment();
                    break;
                case T_STRING:
                    $this->handleString();
                    break;
                default:
                    $this->handle($this->getIterator()->type());
                    break;
            }
            $this->getIterator()->next();
        }

        return $this->getStorage()->format();
    }

    /**
     * @return void
     */
    private function initIterator()
    {
        $tokenFilter         = new TokenFilter(token_get_all($this->source, TOKEN_PARSE));
        $this->tokenIterator = new TokenIterator($tokenFilter->filter());
    }

    /**
     * @return TokenIterator
     */
    private function getIterator() : TokenIterator
    {
        return $this->tokenIterator;
    }

    /**
     * @return Storage
     */
    private function getStorage() : Storage
    {
        return $this->storage;
    }

    /**
     * @return int
     */
    private function getStorageSize() : int
    {
        return $this->getStorage()->length();
    }

    /**
     * @param  string $type
     * @return EntityInterface
     */
    private function initEntity(string $type) : EntityInterface
    {
        $class = __NAMESPACE__ . '\\Entity\\' . ucfirst($type) . 'Entity';
        $class = new $class;

        if ($class instanceof EntityInterface) {
            $class->add($this->getIterator());
            return $class;
        }

        $class = new Entity\StringEntity();
        $class->add($this->getIterator());
        return $class;
    }

    /**
     * @param  EntityInterface $entity
     * @return void
     */
    private function addEntity(EntityInterface $entity)
    {
        $this->getStorage()->add($entity);
    }

    /**
     * @param  string $type
     * @return null|EntityInterface
     */
    private function getLastEntity(string $type)
    {
        return $this->getStorage()->getLast($type);
    }

    /**
     * @param  mixed $token
     * @return mixed
     */
    private function handle($token)
    {
        if (in_array($token, [T_ABSTRACT, T_FINAL])) {
            $this->handleAbstractFinal();
            return;
        }

        if (in_array($token, [T_CLASS, T_INTERFACE, T_TRAIT])) {
            $this->handleObjects();
            return;
        }

        if (in_array($token, [T_PUBLIC, T_PRIVATE, T_PROTECTED, T_STATIC, T_FUNCTION])) {
            $this->handleMethodProperty();
            return;
        }

        if (in_array($token, [T_CONST, T_VAR])) {
            $this->handleConstVar();
        }
    }

    /**
     * @return void
     */
    private function handleOpen()
    {
        if (!$this->openTag) {
            $this->addEntity($this->initEntity(Storage::S_STRING));
            $this->openTag = true;
        }
    }

    /**
     * @return mixed
     */
    private function handleUse()
    {
        $useEntity    = $this->initEntity(Storage::S_USE);
        $objectEntity = $this->getLastEntity(Storage::S_OBJECT);

        if ($objectEntity instanceof Entity\ObjectEntity) {
            $objectEntity->addUse($useEntity);
            return;
        }

        $this->addEntity($useEntity);
    }

    /**
     * @return mixed
     */
    private function handleAbstractFinal()
    {
        if ($this->isAbstractClass() && !$this->isAnonymousClass()) {
            $this->addEntity($this->initEntity(Storage::S_OBJECT));
            return;
        }

        $functionEntity = $this->initEntity(Storage::S_FUNCTION);
        $objectEntity   = $this->getLastEntity(Storage::S_OBJECT);

        if ($objectEntity instanceof Entity\ObjectEntity) {
            $objectEntity->addMethod($functionEntity);
        }
    }

    /**
     * @return void
     */
    private function handleObjects()
    {
        if ($this->isObject() && !$this->isAnonymousClass()) {
            $this->addEntity($this->initEntity(Storage::S_OBJECT));
        }
    }

    /**
     * @return void
     */
    private function handleConstVar()
    {
        $objectEntity = $this->getLastEntity(Storage::S_OBJECT);

        if ($objectEntity instanceof Entity\ObjectEntity) {
            $objectEntity->addProperty($this->initEntity(Storage::S_VARIABLE));
        }
    }

    /**
     * @return void
     */
    private function handleDocComment()
    {
        $objectEntity   = $this->getLastEntity(Storage::S_OBJECT);
        $docblockEntity = $this->initEntity(Storage::S_DOCBLOCK);

        if ($objectEntity instanceof Entity\ObjectEntity) {
            $objectEntity->addDocblock($docblockEntity);
        } elseif ($this->getStorageSize() === 1) {
            $this->addEntity($docblockEntity);
        }
    }

    /**
     * @return mixed
     */
    private function handleMethodProperty()
    {
        try {
            $objectEntity = $this->getLastEntity(Storage::S_OBJECT);

            if ($this->isFunction()) {
                $functionEntity = $this->initEntity(Storage::S_FUNCTION);

                if ($objectEntity instanceof Entity\ObjectEntity) {
                    $objectEntity->addMethod($functionEntity);
                    return;
                }

                $this->addEntity($functionEntity);
                return;
            }

            if ($objectEntity instanceof Entity\ObjectEntity) {
                $objectEntity->addProperty($this->initEntity(Storage::S_VARIABLE));
            }
        } catch (LambdaException $e) {
            return;
        }
    }

    /**
     * @return void
     */
    private function handleString()
    {
        $objectEntity = $this->getLastEntity(Storage::S_OBJECT);

        if (!$objectEntity instanceof Entity\ObjectEntity) {
            $this->addEntity($this->initEntity(Storage::S_STRING));
        }
    }

    /**
     * @return bool
     */
    private function isAbstractClass() : bool
    {
        $startKey   = $this->getIterator()->key();
        $tempTokens = $this->getIterator()->seekUntil(new Criteria(self::SEMICOLON));

        if (in_array('class', $tempTokens)) {
            $this->getIterator()->reset($startKey);
            return true;
        }

        $this->getIterator()->reset($startKey);
        return false;
    }

    /**
     * @return bool
     */
    private function isAnonymousClass() : bool
    {
        $startKey   = $this->getIterator()->key();
        $tempTokens = $this->getIterator()->seekUntil(new Criteria(self::BRACKET_OPEN));

        if (in_array(self::PARENTHESIS_OPEN, $tempTokens)) {
            $open  = 0;
            $close = 0;
            while ($this->getIterator()->valid()) {
                if ($this->getIterator()->current() === self::BRACKET_OPEN) {
                    $open++;
                } elseif ($this->getIterator()->current() === self::BRACKET_CLOSE) {
                    $close++;
                }
                if ($close > $open) {
                    call_user_func_array($this->logger, ['LAMBDA OBJECT (1): ' . implode(' ', $tempTokens)]);
                    return true;
                }
                $this->getIterator()->next();
            }
            call_user_func_array($this->logger, ['LAMBDA OBJECT (2): ' . implode(' ', $tempTokens)]);
            return true;
        }

        call_user_func_array($this->logger, ['NORMAL CLASS: ' . implode(' ', $tempTokens)]);
        $this->getIterator()->reset($startKey);
        return false;
    }

    /**
     * @return bool
     */
    private function isObject() : bool
    {
        $objects    = ['class', 'interface', 'trait'];
        $startKey   = $this->getIterator()->key();
        $tempTokens = $this->getIterator()->seekUntil(new Criteria(self::BRACKET_OPEN));

        foreach ($objects as $type) {
            if (in_array($type, $tempTokens)) {
                call_user_func_array($this->logger, ['NATIVE OBJECT: ' . implode(' ', $tempTokens)]);
                $this->getIterator()->reset($startKey);
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     * @throws LambdaException
     */
    private function isFunction() : bool
    {
        $startKey   = $this->getIterator()->key();
        $tempTokens = $this->getIterator()->seekUntil(new Criteria([self::SEMICOLON, self::BRACKET_OPEN]));
        $lastToken  = (string) $tempTokens[sizeof($tempTokens)-1];

        if ($lastToken === self::SEMICOLON && in_array('function', $tempTokens)) {
            call_user_func_array($this->logger, ['ABSTRACT FUNCTION: ' . implode(' ', $tempTokens)]);
            $this->getIterator()->reset($startKey);
            return true;
        }

        if (in_array('use', $tempTokens) ||
            in_array(self::SCOPE_RESOLUTION, $tempTokens) ||
            $tempTokens[1] === self::PARENTHESIS_OPEN
        ) {
            call_user_func_array($this->logger, ['LAMBDA FUNCTION: ' . implode(' ', $tempTokens)]);
            $open  = 0;
            $close = 0;
            while ($this->getIterator()->valid()) {
                if ($this->getIterator()->current() === self::BRACKET_OPEN) {
                    $open++;
                } elseif ($this->getIterator()->current() === self::BRACKET_CLOSE) {
                    $close++;
                }
                if ($close > $open) {
                    call_user_func_array($this->logger, ['LAMBDA OBJECT: ' . implode(' ', $tempTokens)]);
                    throw new LambdaException();
                }
                $this->getIterator()->next();
            }
            call_user_func_array($this->logger, ['LAMBDA OBJECT (2): ' . implode(' ', $tempTokens)]);
            throw new LambdaException();
        }

        if (in_array(Storage::S_FUNCTION, $tempTokens)) {
            call_user_func_array($this->logger, ['NATIVE FUNCTION: ' . implode(' ', $tempTokens)]);
            $this->getIterator()->reset($startKey);
            return true;
        }
        call_user_func_array($this->logger, ['PROPERTY: ' . implode(' ', $tempTokens)]);
        $this->getIterator()->reset($startKey);
        return false;
    }
}
