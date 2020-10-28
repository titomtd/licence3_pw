<?php


namespace App;


class Languages
{
    public const JAVA = 'java';
    public const PHP = 'php';
    public const PYTHON = 'python';

    public const ALL = [
        self::JAVA,
        self::PHP,
        self::PYTHON,
    ];

    private function __construct()
    {
    }
}