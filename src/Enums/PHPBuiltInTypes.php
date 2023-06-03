<?php

namespace RicardoFabris\LaravelTypeSync\Enums;

enum PHPBuiltInTypes: string
{
    case INT = 'int';
    case FLOAT = 'float';
    case STRING = 'string';
    case BOOL = 'bool';
    case ARRAY = 'array';
    case OBJECT = 'object';
    case MIXED = 'mixed';
    case NULL = 'null';

    public function getTypescriptType()
    {
        return match($this) {
            PHPBuiltInTypes::INT, PHPBuiltInTypes::FLOAT => TypescriptBuiltInTypes::NUMBER->value,
            PHPBuiltInTypes::STRING => TypescriptBuiltInTypes::STRING->value,
            PHPBuiltInTypes::BOOL => TypescriptBuiltInTypes::BOOLEAN->value,
            PHPBuiltInTypes::ARRAY => TypescriptBuiltInTypes::ANY->value . '[]',
            default => TypescriptBuiltInTypes::ANY->value
        };

    }
}
