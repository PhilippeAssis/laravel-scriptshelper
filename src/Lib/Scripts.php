<?php

namespace Wiidoo\ScriptsHelper\Lib;

use Wiidoo\Support\FluentInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\Filesystem\Filesystem;

class Scripts extends FluentInterface
{
    public $name = null;

    public $type = null;

    public $class = null;

    public $lang = null;

    public $params = null;

    public $pluginConfig = [];

    public $cache = [];

    public $templatePath = null;

    public $compiled = null;

    private $blade;

    public function __construct()
    {
        parent::__construct();

        $this->reset();

        $this->blade = new BladeCompiler(new Filesystem(), $this->compiled);
    }

    private function setGlobal()
    {
        global $SCRIPTSHELPERCLASS;
        return $SCRIPTSHELPERCLASS = $this;
    }

    public function __call($name, $arguments)
    {
        parent::__call($name, $arguments);

        if (!parent::validatePropertyChange($name) and !parent::searchNegatedName($name)) {
            $this->params[$name] = $arguments[0];
        }

        return $this->setGlobal();
    }

    public function reset()
    {
        $this->name = "file";

        $this->type = null;

        $this->class = null;

        $this->params = [];

        parent::mergeConfig('wiidoo.scriptshelper');

        if (!$this->lang) {
            $this->lang = Config::get('app.locale');
        }

        if(!$this->templatePath){
            $this->templatePath = resource_path('scripts');
        }

        if(!$this->compiled){
            $this->compiled = storage_path('scripts');
        }

    }

    public function input()
    {
        if (!isset($this->cache[$this->type])) {
            $this->cache[$this->type] = [
                'inputs' => []
            ];
        } elseif (!isset($this->cache[$this->type]['inputs'])) {
            $this->cache[$this->type]['inputs'] = [];
        }

        if (isset($this->pluginConfig[$this->type])) {
            foreach ($this->pluginConfig[$this->type] as $key => $value) {
                if (!isset($this->params[$key])) {
                    $this->params[$key] = $value;
                }
            }

            if (!isset($this->params['lang'])) {
                $this->params['lang'] = $this->lang;
            }
        }

        $new = [
            'name' => $this->name,
            'type' => $this->type,
            'class' => $this->class,
            'params' => $this->params,
            'lang' => $this->lang,
            'id' => $this->type . '-' . count($this->cache[$this->type]['inputs'])
        ];

        $this->cache[$this->type]['inputs'][] = $new;

        $this->reset();

        return $this->generateTemplate('input', $new['type'], $new);

    }

    public function script()
    {
        $scripts = [];

        foreach ($this->cache as $type => $value) {
            if (!isset($value['inputs'])) {
                continue;
            }

            do {
                $scripts[] = $this->generateTemplate('script', $type, current($value['inputs']));
            } while (next($value['inputs']));
        }

        return implode("\n", $scripts);
    }

    private function generateTemplate($template, $type, $input)
    {
        $dirs = [];

        $dirs[] = $this->templatePath . '/' . $type . '.' . $template . '.blade.php';
        $dirs[] = $this->templatePath . '/' . $type . '.' . $template . '.php';

        do {
            $dir = current($dirs);

            if (file_exists($dir)) {
                return $this->invoke($dir, $input);
            }

        } while (next($dirs));

    }

    private function invoke($file, $input)
    {
        $this->blade->compile($file);

        $obLevel = ob_get_level();
        ob_start();

        extract($input);

        if (isset($input['params'])) {
            extract($input['params']);
        }

        try {
            include $this->blade->getCompiledPath($file);
        } catch (Exception $e) {
            $this->handleViewException($e, $obLevel);
        }

        return ltrim(ob_get_clean());

    }


}