<?php

namespace common\traits;

use yii\helpers\BaseInflector;

trait StringyTrait
{
    public function markdownify($text)
    {
        if (empty($text)) {
            return '';
        }

        $parseDown = new \Parsedown();
        return $parseDown->parse($text);
    }

    public function slugify($text)
    {
        return BaseInflector::slug(BaseInflector::transliterate($text), '-');
    }
}