<?php


namespace App;


class Job
{
    public const STUDENT = 'student';
    public const TEACHER = 'teacher';
    public const SEARCHER = 'searcher';

    public const ALL = [
        self::STUDENT,
        self::TEACHER,
        self::SEARCHER,
    ];

    private function __construct()
    {
    }

}