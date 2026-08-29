<?php
namespace App\Config;

define('ROOT_PATH', str_replace('\\', '/', dirname(__DIR__, 3)));

final class Paths
{
    public const ROOT        = ROOT_PATH;
    public const SRC         = ROOT_PATH . '/src';
    public const APP         = ROOT_PATH . '/src/App';
    public const VIEWS       = ROOT_PATH . '/src/App/views';
    public const CONTROLLERS = ROOT_PATH . '/src/App/Controllers';
    public const FRAMEWORK   = ROOT_PATH . '/src/Framework';
    public const CONFIG      = ROOT_PATH . '/src/App/Config';
}
