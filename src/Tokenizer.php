<?php declare(strict_types=1);

namespace AdamMarton\Stub;

use AdamMarton\Stub\Exception\LambdaException;

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
    const SEMICOLON         = ';';

    /**
     * @var string
     */
    protected $source       = '';

    /**
     * @var array
     */
    protected $tokens       = [];

    /**
     * @var mixed
     */
    protected $currentToken;

    /**
     * @var null|Storage
     */
    private $storage        = null;

    /**
     * @var bool
     */
    private $openTag        = false;

    /**
     * @param  string $source
     * @return void
     */
    public function __construct(string $source)
    {
        $this->source = $source;
    }

    /**
     * @return string
     */
    public function parse() : string
    {
        $this->tokens = token_get_all($this->source, TOKEN_PARSE);

        while (sizeof($this->tokens)) {
            $this->next();
            $token = $this->getCurrentToken();

            switch ($token) {
                case T_OPEN_TAG:
                    $this->handleOpen();
                    break;
                case T_NAMESPACE:
                    $this->addEntity($this->initEntity(Storage::S_NAMESPACE));
                    break;
                case T_USE:
                    $this->handleUse();
                    break;
                case T_DOC_COMMENT:
                    $this->handleDocComment();
                    break;
                case T_STRING:
                    $this->addEntity($this->initEntity(Storage::S_STRING));
                    break;
                default:
                    $this->handle($token);
                    break;
            }
        }

        return $this->getStorage()->format();
    }

    /**
     * @return Storage
     */
    private function getStorage() : Storage
    {
        if ($this->storage === null) {
            $this->storage = new Storage();
        }

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
            $class->add($this);
            return $class;
        }

        $class = new Entity\StringEntity();
        $class->add();

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
     * @return mixed
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

        if (in_array($token, [T_CONST, T_VAR])) {
            $this->handleConstVar();
            return;
        }

        if (in_array($token, [T_PUBLIC, T_PRIVATE, T_PROTECTED, T_STATIC, T_FUNCTION])) {
            $this->handleMethodProperty();
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
        $useEntity   = $this->initEntity(Storage::S_USE);
        $classEntity = $this->getLastEntity(Storage::S_CLASS);

        if ($classEntity instanceof Entity\ClassEntity) {
            $classEntity->addUse($useEntity);
            return;
        }

        $this->addEntity($useEntity);
    }

    /**
     * @return mixed
     */
    private function handleAbstractFinal()
    {
        if ($this->isObject()) {
            $this->addEntity($this->initEntity(Storage::S_CLASS));
            return;
        }

        $functionEntity = $this->initEntity(Storage::S_FUNCTION);
        $classEntity    = $this->getLastEntity(Storage::S_CLASS);

        if ($classEntity instanceof Entity\ClassEntity) {
            $classEntity->addMethod($functionEntity);
        }
    }

    /**
     * @return void
     */
    private function handleObjects()
    {
        if ($this->isObject()) {
            $this->addEntity($this->initEntity(Storage::S_CLASS));
        }
    }

    /**
     * @return void
     */
    private function handleConstVar()
    {
        $classEntity = $this->getLastEntity(Storage::S_CLASS);

        if ($classEntity instanceof Entity\ClassEntity) {
            $classEntity->addProperty($this->initEntity(Storage::S_VARIABLE));
        }
    }

    /**
     * @return void
     */
    private function handleDocComment()
    {
        $classEntity    = $this->getLastEntity(Storage::S_CLASS);
        $docblockEntity = $this->initEntity(Storage::S_DOCBLOCK);

        if ($classEntity instanceof Entity\ClassEntity) {
            $classEntity->addDocblock($docblockEntity);
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
            $classEntity = $this->getLastEntity(Storage::S_CLASS);

            if ($this->isFunction()) {
                $functionEntity = $this->initEntity(Storage::S_FUNCTION);

                if ($classEntity instanceof Entity\ClassEntity) {
                    $classEntity->addMethod($functionEntity);
                    return;
                }

                $this->addEntity($functionEntity);
                return;
            }

            $nextNonEmpty = $this->seekToNonEmpty();

            if ($classEntity instanceof Entity\ClassEntity &&
                isset($nextNonEmpty['token']) &&
                $nextNonEmpty['token'] !== '::'
            ) {
                $classEntity->addProperty($this->initEntity(Storage::S_VARIABLE));
            }
        } catch (LambdaException $e) {
            return;
        }
    }

    /**
     * @param  int $key
     * @return mixed
     */
    public function getCurrentToken(int $key = 0)
    {
        return is_array($this->currentToken) ? $this->currentToken[$key] : $this->currentToken;
    }

    /**
     * @param  int $step
     * @return void
     */
    private function next(int $step = 1)
    {
        while ($step > 0) {
            $this->currentToken = array_shift($this->tokens);
            $step--;
        }
    }

    /**
     * @return mixed
     */
    private function nextNonEmpty()
    {
        while (true) {
            $tempToken = array_shift($this->tokens);

            if (!is_array($tempToken)) {
                return $tempToken;
            }

            $tempToken = trim((string) $tempToken[1]);

            if (is_numeric($tempToken) || !empty($tempToken)) {
                return $tempToken;
            }
        }
    }

    /**
     * @param  string|array $token
     * @return array
     */
    public function advanceTo($token) : array
    {
        $tempTokens = [];

        while (true) {
            $tempToken = $this->nextNonEmpty();

            if ($this->inOrEqual($tempToken, $token)) {
                return $tempTokens;
            }

            $tempTokens[] = $tempToken;
        }

        return $tempTokens;
    }

    /**
     * @param  string|array $token
     * @return array
     */
    public function seekTo($token) : array
    {
        $tempTokens = [];

        foreach ($this->tokens as $tempToken) {
            $tempToken = is_array($tempToken) ? (string) $tempToken[1] : (string) $tempToken;

            if ($this->inOrEqual($tempToken, $token)) {
                return array_filter(
                    $tempTokens,
                    function (string $value, int $key) : bool {
                        return !empty(trim($value));
                    },
                    ARRAY_FILTER_USE_BOTH
                );
            }

            $tempTokens[] = $tempToken;
        }

        return $tempTokens;
    }

    /**
     * @return array|null
     */
    private function seekToNonEmpty()
    {
        $iterator = 0;

        foreach ($this->tokens as $tempToken) {
            $tempToken = is_array($tempToken) ? (string) $tempToken[1] : (string) $tempToken;
            $iterator++;

            if (is_numeric($tempToken) || !empty(trim($tempToken))) {
                return [
                    'iterator' => $iterator,
                    'token'    => $tempToken
                ];
            }
        }
    }

    /**
     * @param  mixed $needle
     * @param  mixed $haystack
     * @return bool
     */
    private function inOrEqual($needle, $haystack)
    {
        return (is_array($haystack)) ? in_array($needle, $haystack) : $needle === $haystack;
    }

    /**
     * @return bool
     */
    private function isObject() : bool
    {
        $objects      = ['class', 'interface', 'trait'];
        $seek         = $this->seekTo([self::SEMICOLON, self::BRACKET_OPEN]);
        $seekNonEmpty = $this->seekToNonEmpty();
        
        if ($this->getCurrentToken(1) === 'class' && in_array(self::PARENTHESIS_OPEN, $this->seekTo([self::BRACKET_OPEN])) ||
            is_array($seekNonEmpty) && $seekNonEmpty['token'] === self::BRACKET_OPEN
        ) {
            $iterator = 1;
            $opening  = 0;
            $closing  = 0;
            $tempArr  = [];
            foreach ($this->tokens as $tempToken) {
                $iterator++;
                $tempToken = is_array($tempToken) ? (string) $tempToken[1] : (string) $tempToken;
                $tempArr[] = $tempToken;

                if ($tempToken === self::BRACKET_OPEN) {
                    $opening++;
                }

                if ($tempToken === self::BRACKET_CLOSE) {
                    $closing++;
                }

                if ($closing > $opening) {
                    $this->next($iterator);
                    return false;
                }
            }
            return false;
        }

        $header  = array_merge([$this->getCurrentToken(1)], $seek);
        
        foreach ($objects as $type) {
            if (in_array($type, $header)) {
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
        $seek         = $this->seekTo([self::SEMICOLON, self::BRACKET_OPEN]);
        $currentToken = (string) $this->getCurrentToken(1);

        if (in_array('use', $seek) ||
            ($currentToken === 'static' && !in_array(Storage::S_FUNCTION, $seek)) ||
            ($currentToken === 'static' && (isset($seek[0]) && $seek[0] === self::PARENTHESIS_OPEN)) ||
            ($seek[1] === self::PARENTHESIS_OPEN) ||
            (in_array('::', $seek))
        ) {
            throw new LambdaException();
        }

        $header = array_merge([$currentToken], $seek);

        if (in_array(Storage::S_FUNCTION, $header)) {
            return true;
        }

        return false;
    }
}
