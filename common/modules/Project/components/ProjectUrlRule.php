<?php

namespace common\modules\Project\components;

use common\models\User;
use common\modules\Project\models\Group;
use common\modules\Project\models\Project;
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
        $params = [];

        if (preg_match('%^(\w+)(/(\w+))?$%', $pathInfo, $matches)) {
            if (User::findByUsername($matches[1])) {
                $params['user'] = $matches[1];
            } elseif (Group::findByCode($matches[1])) {
                $params['group'] = $matches[1];
            }

            if (!empty($params) && isset($matches[3])) {
                if (Project::findByCode($matches[3])) {
                    $params['project'] = $matches[3];
                }
                // TODO: check rights
            } else {
                return false;
            }

            if (isset($params['project'])) {
                return ['project/project/show', $params];
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