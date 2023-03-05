<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\TemplateCompiler\NodeFactory;

use PhpParser\Comment\Doc;
use PhpParser\Node\Stmt\Nop;
use TomasVotruba\Bladestan\TemplateCompiler\ValueObject\VariableAndType;

final class VarDocNodeFactory
{
    /**
     * @param VariableAndType[] $variablesAndTypes
     * @return Nop[]
     */
    public function createDocNodes(array $variablesAndTypes): array
    {
        $docNodes = [];
        foreach ($variablesAndTypes as $variableAndType) {
            $docNodes[$variableAndType->getVariable()] = $this->createDocNop($variableAndType);
        }

        return array_values($docNodes);
    }

    private function createDocNop(VariableAndType $variableAndType): Nop
    {
        $prependVarTypesDocBlocks = sprintf(
            '/** @var %s $%s */',
            $variableAndType->getTypeAsString(),
            $variableAndType->getVariable()
        );

        // doc types node
        $docNop = new Nop();
        $docNop->setDocComment(new Doc($prependVarTypesDocBlocks));

        return $docNop;
    }
}
