<?php

namespace common\modules\Project\components;


use Yii;
use yii\base\InvalidConfigException;
use yii\web\GroupUrlRule;
use yii\web\UrlRuleInterface;

/**
 * @class VerbGroupUrlRule
 * @package common\modules\Project\components
 * 
 * Extended GroupUrlRule with possibility to parse verbs as rest\UrlRule does it.
 * 
 * For example:
 * - '/' => 'project/explore'
 * - 'POST /' => 'project/create'
 * - 'GET new' => 'project/new'
 */
class VerbGroupUrlRule extends GroupUrlRule
{

    /**
     * @inheritdoc
     */
    protected function createRules()
    {
        $rules = [];
        foreach ($this->rules as $key => $rule) {
            if (!is_array($rule)) {
                $rule = $this->createRuleWithVerb($key, $rule);
            } elseif (isset($rule['pattern'], $rule['route'])) {
                $rule['pattern'] = ltrim($this->prefix . '/' . $rule['pattern'], '/');
                $rule['route'] = ltrim($this->routePrefix . '/' . $rule['route'], '/');
            }

            $rule = Yii::createObject(array_merge($this->ruleConfig, $rule));
            if (!$rule instanceof UrlRuleInterface) {
                throw new InvalidConfigException('URL rule class must implement UrlRuleInterface.');
            }
            $rules[] = $rule;
        }
        return $rules;
    }

    /**
     * Explode verb, pattern and route to params array
     * @param $pattern
     * @param $route
     * @return array
     */
    protected function createRuleWithVerb($pattern, $route)
    {
        $verbs = 'GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS';

        $verb = 'GET';

        if (preg_match("/^($verbs)(?:\\s+(.*))?$/", $pattern, $matches)) {
            $verb = $matches[1];
            if (isset($matches[2])) {
                $pattern = $matches[2];
            }
        }

        return [
            'pattern' => ltrim($this->prefix . '/' . $pattern, '/'),
            'route' => ltrim($this->routePrefix . '/' . $route, '/'),
            'verb' => $verb
        ];
    }
}