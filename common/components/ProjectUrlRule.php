<?php

namespace common\components;

use common\models\Project;
use yii\web\CompositeUrlRule;
use yii\web\Request;
use yii\web\UrlManager;

/**
 * ProjectUrlRule represents all routes relating to projects.
 * Each project has a slug which looks like /user/project or /group/project.
 * ProjectUrlRule has primary patterns which correspond to a slug.
 * It is a composite URL rule which may have nested rules. Nested rules are defined like standard rules do,
 * e.g. '<VERB> <pattern>' => '<controller>/<action>
 */
class ProjectUrlRule extends CompositeUrlRule
{
    public $rules;

    /**
     * Parses the given request and returns the corresponding route and parameters.
     * @param UrlManager $manager the URL manager
     * @param Request $request the request component
     * @return array|boolean the parsing result. The route and the parameters are returned as an array.
     * If false, it means this rule cannot be used to parse this path info.
     */
    public function parseRequest($manager, $request)
    {
        $pathInfo = $request->getPathInfo();
        $verb = $request->getMethod();

        if (preg_match('%^([\w\d-]+/[\w\d-]+)(/([\w/]+))?$%', $pathInfo, $matches)) {
            $project = Project::findBySlug('/' . $matches[1]);

            if (!$project) {
                return false;
            }

            if (isset($matches[3])) {
                return $this->mapProjectRoute($matches[3], $verb, $project);
            }

            return $this->parseProjectRequest($verb, $project);
        }

        return false;
    }

    /**
     * Matches and parses nested projects routes
     * @param $pattern
     * @param $verb
     * @param Project $project
     * @return array|bool
     */
    private function mapProjectRoute($pattern, $verb, $project)
    {
        foreach ($this->rules as $rule) {
            if ($rule['pattern'] == $pattern && $rule['verb'] == $verb) {
                return [$rule['route'], compact('project')];
            }
        }

        return false;
    }

    /**
     * Creates a URL according to the given route and parameters.
     * @param UrlManager $manager the URL manager
     * @param string $route the route. It should not have slashes at the beginning or the end.
     * @param array $params the parameters
     * @return string|boolean the created URL, or false if this rule cannot be used for creating this URL.
     */
    public function createUrl($manager, $route, $params)
    {
        if (isset($params['project'])) {
            $slug = ltrim($params['project']->slug, '/');
            foreach ($this->rules as $rule) {
                if ($route == $rule['route']) {
                    return $slug . '/' . $rule['pattern'];
                }
            }

            return $slug;
        }

        return false;
    }

    /**
     * Creates the URL rules that should be contained within this composite rule.
     * Prepares each rule by defining verb, pattern and route. Verb should be in pattern part, e.g. GET pattern
     * @return array $rules
     */
    protected function createRules()
    {
        $rules = [];

        $verbs = 'GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS';

        foreach ($this->rules as $key => $rule) {
            $pattern = $key;

            if (preg_match("/^((?:($verbs),)*($verbs))\\s+(.*)$/", $key, $matches)) {
                $verb = $matches[3];
                $pattern = $matches[4];
            } else {
                $verb = 'GET';
            }
            $rule = [
                'pattern' => $pattern,
                'route' => $rule,
                'verb' => $verb,
            ];

            $rules[] = $rule;
        }

        return $rules;
    }

    /**
     * Maps primary projects routes based on verb
     * @param $verb
     * @param $project
     * @return array
     */
    private function parseProjectRequest($verb, $project)
    {
        switch ($verb) {
            case 'DELETE':
                return ['project/delete', compact('project')];
            default:
                return ['project/show', compact('project')];
        }
    }
}