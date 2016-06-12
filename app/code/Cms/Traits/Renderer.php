<?php

namespace Seahinet\Cms\Traits;

use Seahinet\Cms\ViewModel\Block;

/**
 * Replace variables from cms
 */
trait Renderer
{

    /**
     * Replace variables
     * 
     * @param string $content
     * @param array $vars
     * @return string
     */
    protected function replace($content, array $vars = [])
    {
        if ($content) {
            preg_match_all('#{{\s*(?P<type>[^\s\}\'\"]+)(?P<param>(?:\s+[^\s\}]+)*)}}#', $content, $matches);
            $replace = [];
            if (count($matches[0])) {
                foreach ($matches[0] as $key => $src) {
                    if (isset($vars[$matches['type'][$key]])) {
                        $replace[$src] = $vars[$matches['type'][$key]];
                    } else if (is_callable([$this, 'replace' . $matches['type'][$key]])) {
                        $replace[$src] = call_user_func([$this, 'replace' . $matches['type'][$key]], $matches['param'][$key]);
                    } else {
                        $replace[$src] = '';
                    }
                }
            }
            if (count($replace)) {
                return str_replace(array_keys($replace), array_values($replace), $content);
            }
        }
        return $content;
    }

    /**
     * Replace block variables
     * 
     * @param string $param
     * @return string
     */
    protected function replaceBlock($param = '')
    {
        if ($param) {
            preg_match_all('#\s+(?P<key>[^\=]+)\=([\'\"])(?P<value>[^\2]+?)\2#', $param, $matches);
            $params = array_combine($matches['key'], $matches['value']);
            if ((!isset($params['type']) || trim($params['type'], '\\') === 'Seahinet\\Cms\\ViewModel\\Block') && isset($params['id'])) {
                $block = new Block;
                $block->setBlockId($params['id']);
//        } else if (isset($params['type'])) {
//            $block = new $params['type'];
//            if (isset($params['template'])) {
//                $block->setTemplate($params['template']);
//            }
            } else {
                return '';
            }
            return $block->__toString();
        }
        return '';
    }

}
