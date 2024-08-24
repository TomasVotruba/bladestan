<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\VerbosityLevel;
use TomasVotruba\Bladestan\TemplateCompiler\NodeFactory\VarDocNodeFactory;

final class ViewVariableAnalyzer
{
    /**
     * Resolve view function call if the data is a variable.
     *
     * @param Expr $expr
     * @param Scope $scope
     * @return Array_
     */
    public function resolve(Expr $expr, Scope $scope): Array_
    {
        $array = new Array_();

        $type = $scope->getType($expr);

        $keyTypes = array_map(function ($keyType) {
            return $keyType->getValue();
        }, $type->getKeyTypes());

        foreach (array_combine($keyTypes, $type->getValueTypes()) as $key => $value) {
            VarDocNodeFactory::setDocBlock($key, $value->describe(VerbosityLevel::typeOnly()));
            $array->items[] = new ArrayItem(new Variable($key), new String_($key));
        }

        return $array;
    }
}
