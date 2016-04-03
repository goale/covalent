<?php

namespace frontend\assets;


use yii\web\AssetBundle;

class MarkedAsset extends AssetBundle
{
    public $sourcePath = '@bower/marked';

    public $js = [
        'marked.min.js',
    ];

}