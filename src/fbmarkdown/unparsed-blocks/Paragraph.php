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

use type Facebook\Markdown\Blocks\Paragraph as ASTNode;
use namespace Facebook\Markdown\Inlines;
use namespace HH\Lib\{C, Str, Vec};

final class Paragraph extends LeafBlock {
  public function __construct(
    private string $content,
  ) {
  }

  public static function consume(
    Context $context,
    Lines $lines,
  ): ?(Paragraph, Lines) {
    if ($context->isInParagraphContinuation()) {
      return null;
    }

    list($first, $lines) = $lines->getFirstLineAndRest();
    $matched = vec[$first];

    while (!$lines->isEmpty()) {
      list($next, $rest) = $lines->getFirstLineAndRest();
      if (Lines::isBlankLine($next)) {
        break;
      }

      if (!_Private\is_paragraph_continuation_text($context, $lines)) {
        break;
      }
      $matched[] = $next;
      $lines = $rest;
    }

    return tuple(
      new self(Str\join($matched, "\n")),
      $lines,
    );
  }

  public function withParsedInlines(Inlines\Context $ctx): ASTNode {
    return new ASTNode(Inlines\parse($ctx, $this->content));
  }
}
