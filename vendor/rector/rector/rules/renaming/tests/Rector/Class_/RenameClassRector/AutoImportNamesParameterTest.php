<?php

declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\Class_\RenameClassRector;

use Iterator;
use Rector\CodeQuality\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector;
use Rector\Core\Configuration\Option;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Renaming\Rector\Class_\RenameClassRector;
use Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\NewClass;
use Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\OldClass;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see \Rector\PostRector\Rector\NameImportingPostRector
 */
final class AutoImportNamesParameterTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->setParameter(Option::AUTO_IMPORT_NAMES, true);

        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureAutoImportNames');
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            # this class causes to "partial_expression.php.inc" to fail
            SimplifyEmptyArrayCheckRector::class => [],
            RenameClassRector::class => [
                '$oldToNewClasses' => [
                    OldClass::class => NewClass::class,
                ],
            ],
        ];
    }
}
