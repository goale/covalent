<?php

namespace common\components;

use common\models\Project;
use yii\web\Request;
use yii\web\UrlManager;
use yii\web\UrlRuleInterface;

class ProjectUrlRule implements UrlRuleInterface
{
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

        if (preg_match('%^([\w\d-]+/[\w\d-]+)(/(\w+))?$%', $pathInfo, $matches)) {
            $project = Project::findBySlug('/' . $matches[1]);

            if (!$project) {
                return false;
            }

            \Yii::trace($project);

            if (isset($matches[3])) {
                return $this->mapProjectRoute($matches[3], $verb, $project);
            }

            return ['project/show', compact('project')];
        }

        return false;
    }

    /**
     * Create route to controller mapping based on regexp matching
     * @param $route
     * @param $verb
     * @param Project $project
     * @return array|bool
     */
    private function mapProjectRoute($route, $verb, $project)
    {
        if ($route == 'users') {
            switch ($verb) {
                case 'POST':
                    return ['project/add-member', compact('project')];
                case 'PATCH':
                    return ['project/change-member-role', compact('project')];
                case 'DELETE':
                    return ['project/delete-member', compact('project')];
                default:
                    return false;
            }
        }

        // TODO: add /edit mappings
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
        if ($route == 'project/show') {
            if (isset($params['user'])) {
                return $params['user'] . '/' . $params['project'];
            }

            if (isset($params['group'])) {
                return $params['group'] . '/' . $params['project'];
            }
        }

        return false;
    }
}