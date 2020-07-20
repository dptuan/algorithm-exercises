<?php

declare(strict_types=1);

namespace Rector\Naming\Naming;

use Nette\Utils\Strings;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StaticType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\PHPStan\Type\SelfObjectType;
use Rector\PHPStan\Type\ShortenedObjectType;

final class PropertyNaming
{
    /**
     * @var string[]
     */
    private const EXCLUDED_CLASSES = ['#Closure#', '#^Spl#', '#FileInfo#', '#^std#', '#Iterator#', '#SimpleXML#'];

    /**
     * @var string
     */
    private const INTERFACE = 'Interface';

    public function getExpectedNameFromType(Type $type): ?string
    {
        if ($type instanceof UnionType) {
            $type = $this->unwrapNullableType($type);
        }

        if (! $type instanceof TypeWithClassName) {
            return null;
        }

        if ($type instanceof SelfObjectType) {
            return null;
        }

        if ($type instanceof StaticType) {
            return null;
        }

        $className = $this->getClassName($type);

        foreach (self::EXCLUDED_CLASSES as $excludedClass) {
            if (Strings::match($className, $excludedClass)) {
                return null;
            }
        }

        $shortClassName = $this->resolveShortClassName($className);
        $shortClassName = $this->removePrefixesAndSuffixes($shortClassName);

        // if all is upper-cased, it should be lower-cased
        if ($shortClassName === strtoupper($shortClassName)) {
            $shortClassName = strtolower($shortClassName);
        }

        // remove "_"
        $shortClassName = Strings::replace($shortClassName, '#_#', '');
        $shortClassName = $this->normalizeUpperCase($shortClassName);

        return lcfirst($shortClassName);
    }

    /**
     * @param ObjectType|string $objectType
     */
    public function fqnToVariableName($objectType): string
    {
        $className = $this->resolveClassName($objectType);

        $shortName = $this->fqnToShortName($className);
        $shortName = $this->removeInterfaceSuffixPrefix($className, $shortName);

        return lcfirst($shortName);
    }

    /**
     * @source https://stackoverflow.com/a/2792045/1348344
     */
    public function underscoreToName(string $underscoreName): string
    {
        $camelCaseName = str_replace('_', '', ucwords($underscoreName, '_'));

        return lcfirst($camelCaseName);
    }

    private function removePrefixesAndSuffixes(string $shortClassName): string
    {
        // is SomeInterface
        if (Strings::endsWith($shortClassName, 'Interface')) {
            $shortClassName = Strings::substring($shortClassName, 0, -strlen('Interface'));
        }

        // is ISomeClass
        if ($this->isPrefixedInterface($shortClassName)) {
            $shortClassName = Strings::substring($shortClassName, 1);
        }

        // is AbstractClass
        if (Strings::startsWith($shortClassName, 'Abstract')) {
            $shortClassName = Strings::substring($shortClassName, strlen('Abstract'));
        }

        return $shortClassName;
    }

    private function isPrefixedInterface(string $shortClassName): bool
    {
        if (strlen($shortClassName) <= 3) {
            return false;
        }

        if (! Strings::startsWith($shortClassName, 'I')) {
            return false;
        }

        return ctype_upper($shortClassName[1]) && ctype_lower($shortClassName[2]);
    }

    private function getClassName(TypeWithClassName $typeWithClassName): string
    {
        if ($typeWithClassName instanceof ShortenedObjectType) {
            return $typeWithClassName->getFullyQualifiedName();
        }

        return $typeWithClassName->getClassName();
    }

    private function resolveShortClassName(string $className): string
    {
        if (Strings::contains($className, '\\')) {
            return Strings::after($className, '\\', -1);
        }

        return $className;
    }

    private function normalizeUpperCase(string $shortClassName): string
    {
        // turns $SOMEUppercase => $someUppercase
        for ($i = 0; $i <= strlen($shortClassName); ++$i) {
            if (ctype_upper($shortClassName[$i]) && $this->isNumberOrUpper($shortClassName[$i + 1])) {
                $shortClassName[$i] = strtolower($shortClassName[$i]);
            } else {
                break;
            }
        }

        return $shortClassName;
    }

    /**
     * E.g. null|ClassType → ClassType
     */
    private function unwrapNullableType(UnionType $unionType): ?Type
    {
        if (count($unionType->getTypes()) !== 2) {
            return null;
        }

        if (! $unionType->isSuperTypeOf(new NullType())->yes()) {
            return null;
        }

        foreach ($unionType->getTypes() as $unionedType) {
            if ($unionedType instanceof NullType) {
                continue;
            }

            return $unionedType;
        }

        return null;
    }

    private function isNumberOrUpper(string $char): bool
    {
        if (ctype_upper($char)) {
            return true;
        }

        return ctype_digit($char);
    }

    private function fqnToShortName(string $fqn): string
    {
        if (! Strings::contains($fqn, '\\')) {
            return $fqn;
        }

        /** @var string $lastNamePart */
        $lastNamePart = Strings::after($fqn, '\\', - 1);
        if (Strings::endsWith($lastNamePart, self::INTERFACE)) {
            return Strings::substring($lastNamePart, 0, - strlen(self::INTERFACE));
        }

        return $lastNamePart;
    }

    private function removeInterfaceSuffixPrefix(string $className, string $shortName): string
    {
        // remove interface prefix/suffix
        if (! interface_exists($className)) {
            return $shortName;
        }

        // starts with "I\W+"?
        if (Strings::match($shortName, '#^I[A-Z]#')) {
            return Strings::substring($shortName, 1);
        }

        if (Strings::match($shortName, '#Interface$#')) {
            return Strings::substring($shortName, -strlen(self::INTERFACE));
        }

        return $shortName;
    }

    /**
     * @param ObjectType|string $objectType
     */
    private function resolveClassName($objectType): string
    {
        if ($objectType instanceof ObjectType) {
            return $objectType->getClassName();
        }

        return $objectType;
    }
}
