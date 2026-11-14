<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Translation;

use Netgen\Bundle\RemoteMediaBundle\Translation\VueTranslations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;

use function array_diff;
use function array_keys;
use function array_unique;
use function array_values;
use function file_get_contents;
use function in_array;
use function is_array;
use function preg_match;
use function preg_match_all;
use function sort;

#[CoversClass(VueTranslations::class)]
final class VueTranslationsTest extends TestCase
{
    private const ROOT_DIR = __DIR__ . '/../../..';

    public function testTranslate(): void
    {
        $translatorMock = $this->createMock(TranslatorInterface::class);
        $translatorMock
            ->method('trans')
            ->willReturnCallback(
                static fn (string $id, array $parameters, ?string $domain): string => $domain . ':' . $id,
            );

        $translations = (new VueTranslations($translatorMock))->translate();

        self::assertSame(array_keys(VueTranslations::MESSAGES), array_keys($translations));

        foreach (VueTranslations::MESSAGES as $key => $messageId) {
            self::assertSame('ngremotemedia:' . $messageId, $translations[$key]);
        }
    }

    /**
     * The Vue app reads its labels as `translations.<key>` (or `t.<key>` in the
     * upload panel). Every such key must be in the list, and the list must not
     * hold keys that nothing reads.
     */
    public function testKeysMatchTheKeysTheVueAppReads(): void
    {
        $usedKeys = $this->findKeysUsedByVueApp();
        $listedKeys = array_keys(VueTranslations::MESSAGES);

        self::assertSame([], array_values(array_diff($usedKeys, $listedKeys)), 'Keys used by the Vue app are missing from VueTranslations::MESSAGES');
        self::assertSame([], array_values(array_diff($listedKeys, $usedKeys)), 'VueTranslations::MESSAGES contains keys the Vue app does not use');
    }

    #[DataProvider('localeDataProvider')]
    public function testEveryMessageIsTranslated(string $locale): void
    {
        $catalogue = $this->flatten(
            Yaml::parseFile(self::ROOT_DIR . '/bundle/Resources/translations/ngremotemedia.' . $locale . '.yaml'),
        );

        foreach (VueTranslations::MESSAGES as $key => $messageId) {
            self::assertArrayHasKey($messageId, $catalogue, 'Missing "' . $locale . '" translation for "' . $key . '"');
        }
    }

    public static function localeDataProvider(): iterable
    {
        return [
            ['en'],
            ['no'],
            ['hr'],
        ];
    }

    /**
     * The CKEditor plugin defaults are overridden by the /configuration
     * endpoint, which only sends the keys from the list.
     */
    public function testCKEditorDefaultsOnlyUseListedKeys(): void
    {
        $constants = (string) file_get_contents(self::ROOT_DIR . '/bundle/Resources/public/js/ckeditor-plugin/constants.js');

        self::assertSame(1, preg_match('/translations: \{(.*?)\n    \},/s', $constants, $matches));
        preg_match_all("/^\\s*'([a-z_]+)':/m", $matches[1], $keyMatches);

        self::assertNotEmpty($keyMatches[1]);
        self::assertSame([], array_values(array_diff($keyMatches[1], array_keys(VueTranslations::MESSAGES))));
    }

    #[DataProvider('templateDataProvider')]
    public function testTemplatesUseTheSharedList(string $template): void
    {
        $content = (string) file_get_contents(self::ROOT_DIR . '/bundle/Resources/views/app/' . $template);

        self::assertStringContainsString("'translations': ngrm_vue_translations(),", $content);
        self::assertStringNotContainsString('ngrm.edit.vue.', $content);
    }

    public static function templateDataProvider(): iterable
    {
        return [
            ['interactions.html.twig'],
            ['select_folder.html.twig'],
        ];
    }

    /**
     * @return string[]
     */
    private function findKeysUsedByVueApp(): array
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(self::ROOT_DIR . '/frontend/src', RecursiveDirectoryIterator::SKIP_DOTS),
        );

        $keys = [];

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            if (!in_array($file->getExtension(), ['vue', 'js'], true)) {
                continue;
            }

            preg_match_all('/(?:\btranslations|\bt)\.([a-z][a-z_]+)/', (string) file_get_contents($file->getPathname()), $matches);

            foreach ($matches[1] as $key) {
                $keys[] = $key;
            }
        }

        $keys = array_unique($keys);
        sort($keys);

        return $keys;
    }

    /**
     * @return array<string, string>
     */
    private function flatten(array $messages, string $prefix = ''): array
    {
        $flattened = [];

        foreach ($messages as $key => $value) {
            if (is_array($value)) {
                $flattened += $this->flatten($value, $prefix . $key . '.');

                continue;
            }

            $flattened[$prefix . $key] = (string) $value;
        }

        return $flattened;
    }
}
