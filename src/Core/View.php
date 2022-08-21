<?php

declare(strict_types=1);

namespace App\Core;

use App\Exception\AppException;
use App\Exception\ViewBuildException;
use App\Kernel;

final class View
{
    /**
     * @var string|null
     */
    private ?string $_layout_file;

    /**
     * @var string|null
     */
    private ?string $_view_file;

    /**
     * @var string|mixed|null
     */
    private ?string $_title;

    /**
     * @var array
     */
    private array $_blocks;

    /**
     * @var string|null
     */
    private ?string $_buffer;

    /**
     * @var ViewParams
     */
    private ViewParams $_vp;

    /**
     * @param string $class
     * @param string $name
     * @param array $params
     * @return static
     * @throws AppException
     */
    public static function build(string $class, string $name, array $params = []): self
    {
        return new self($class, $name, $params);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        ob_start();
        include($this->_view_file);
        include($this->_layout_file);

        return ob_get_clean();
    }

    /**
     * @param string $class
     * @param string $name
     * @param array $params
     * @param bool $noLayout
     * @throws AppException
     */
    private function __construct(string $class, string $name, array $params = [], bool $noLayout = false)
    {
        $this->_view_file = $this->parseViewPath($class, $name);
        $this->_layout_file = $this->parseLayoutPath($class, $noLayout);
        $this->_title = $params['title'] ?? 'Document';
        $this->_buffer = null;
        $this->_blocks = [];
        $this->_vp = new ViewParams($params);
    }

    /**
     * @param string $class
     * @param string $name
     * @return string
     * @throws AppException
     */
    private function parseViewPath(string $class, string $name): string
    {
        $dir = mb_strtolower(str_replace(["App\\Controller\\", 'Controller'], ['', ''], $class));
        $result = Kernel::VIEW_PATH . '/' . $dir . '/' . $name . '.php';
        if (!file_exists($result)) {
            throw new ViewBuildException('View "' . $dir . '::' . $name . '" not found', 404);
        }

        return $result;
    }

    /**
     * @param string $class
     * @param bool $noLayout
     * @return string|null
     * @throws AppException
     */
    private function parseLayoutPath(string $class, bool $noLayout): ?string
    {
        if ($noLayout === true) {
            return null;
        }

        $dir = mb_strtolower(str_replace(["App\\Controller\\", 'Controller'], ['', ''], $class));
        $result = Kernel::VIEW_PATH . '/layout/' . $dir . '.php';
        if (!file_exists($result)) {
            throw new ViewBuildException('Layout "' . $dir . '" not found', 404);
        }

        return $result;
    }

    /**
     * @return string
     */
    private function getTitle(): string
    {
        return $this->_title;
    }

    /**
     * @param string $key
     * @return string|null
     * @throws AppException
     */
    private function content(string $key): ?string
    {
        $content = $this->_blocks[$key] ?? null;
        if (empty($content)) {
            throw new ViewBuildException('Block "' . $key . '" does not exists');
        }

        return $content;
    }

    /**
     * @param string|null $key
     * @throws AppException
     */
    private function start(?string $key = null): void
    {
        if (empty($key)) {
            throw new ViewBuildException('Block key should be specified');
        }

        $this->_buffer = $key;
        ob_start();
    }

    /**
     * @throws AppException
     */
    private function end(): void
    {
        if (empty($this->_buffer)) {
            throw new ViewBuildException('No block started yet');
        }

        $this->_blocks[$this->_buffer] = ob_get_clean();
        $this->_buffer = null;
    }
}
