<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\ClassStringType;
use PHPStan\Type\FunctionTypeSpecifyingExtension;
use PHPStan\Type\NeverType;
use PHPStan\Type\TypeCombinator;

class ClassExistsFunctionTypeSpecifyingExtension implements FunctionTypeSpecifyingExtension, TypeSpecifierAwareExtension
{

	/** @var TypeSpecifier */
	private $typeSpecifier;

	public function isFunctionSupported(
		FunctionReflection $functionReflection,
		FuncCall $node,
		TypeSpecifierContext $context
	): bool
	{
		return in_array($functionReflection->getName(), [
			'class_exists',
			'interface_exists',
			'trait_exists',
		], true) && isset($node->args[0]) && $context->truthy();
	}

	public function specifyTypes(FunctionReflection $functionReflection, FuncCall $node, Scope $scope, TypeSpecifierContext $context): SpecifiedTypes
	{
		$argType = $scope->getType($node->args[0]->value);
		$classStringType = new ClassStringType();
		if (TypeCombinator::intersect($argType, $classStringType) instanceof NeverType) {
			return new SpecifiedTypes();
		}

		return $this->typeSpecifier->create(
			$node->args[0]->value,
			$classStringType,
			$context
		);
	}

	public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
	{
		$this->typeSpecifier = $typeSpecifier;
	}

}