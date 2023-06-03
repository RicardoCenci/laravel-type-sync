<?php

namespace RicardoFabris\LaravelTypeSync\Enums;

enum TypescriptBuiltInTypes: string
{
    case BOOLEAN = 'boolean';
    case STRING = 'string';
    case NUMBER = 'number';
    case BIGINT = 'bigint';
    case ANY = 'any';
    case NULL = 'null';
}
