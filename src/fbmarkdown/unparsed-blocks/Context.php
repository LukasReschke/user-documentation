<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the BSD-style license found in the
 *  LICENSE file in the root directory of this source tree. An additional grant
 *  of patent rights can be found in the PATENTS file in the same directory.
 *
 */

namespace Facebook\Markdown\UnparsedBlocks;

use namespace HH\Lib\{C, Keyset, Vec};

class Context {
  const keyset<classname<Block>> ALL_BLOCK_TYPES = keyset[
    TableExtension::class,
    BlankLine::class,
    ATXHeading::class,
    FencedCodeBlock::class,
    HTMLBlock::class,
    IndentedCodeBlock::class,
    LinkReferenceDefinition::class,
    BlockQuote::class,
    ThematicBreak::class,
    ListOfItems::class,
    SetextHeading::class,
    Paragraph::class,
  ];

  private keyset<classname<Block>> $blockTypes;

  public function __construct() {
    $this->blockTypes = self::ALL_BLOCK_TYPES;
  }

  public function resetFileData(): this {
    $this->file = null;
    $this->linkReferenceDefinitions = dict[];
    $this->paragraphStack = vec[];
    return $this;
  }

  public function prependBlockTypes(classname<Block> ...$blocks): this {
    $this->blockTypes = Keyset\union($blocks, $this->blockTypes);
    return $this;
  }

  private dict<string, LinkReferenceDefinition> $linkReferenceDefinitions
    = dict[];

  public function getLinkReferenceDefinition(
    string $key,
  ): ?LinkReferenceDefinition {
    $key = LinkReferenceDefinition::normalizeKey($key);
    return $this->linkReferenceDefinitions[$key] ?? null;
  }

  public function addLinkReferenceDefinition(
    LinkReferenceDefinition $def,
  ): this {
    if (!C\contains_key($this->linkReferenceDefinitions, $def->getKey())) {
      $this->linkReferenceDefinitions[$def->getKey()] = $def;
    }
    return $this;
  }

  private ?string $file;

  public function setFilePath(string $file): this {
    $this->file = $file;
    return $this;
  }

  public function getFilePath(): ?string {
    return $this->file;
  }

  private bool $isHtmlEnabled = false;

  public function enableHTML_UNSAFE(): this {
    $this->isHtmlEnabled = true;
    return $this;
  }

  public function isHTMLEnabled(): bool {
    return $this->isHtmlEnabled;
  }

  public function getBlockTypes(): keyset<classname<Block>> {
    return $this->blockTypes;
  }

  private vec<bool> $paragraphStack = vec[];

  public function isInParagraphContinuation(): bool {
    return C\last($this->paragraphStack) ?? false;
  }

  public function pushParagraphContinuation(bool $in_continuation): this {
    $this->paragraphStack[] = $in_continuation;
    return $this;
  }

  public function popParagraphContinuation(): this {
    $this->paragraphStack = Vec\take(
      $this->paragraphStack,
      C\count($this->paragraphStack) - 1,
    );
    return $this;
  }

  public function getListItemTypes(): keyset<classname<ListItem>> {
    // TaskListItemExtension will also return normal ListItems; if we support
    // removing extensions, this will need to change.
    return keyset[TaskListItemExtension::class];
  }
}
