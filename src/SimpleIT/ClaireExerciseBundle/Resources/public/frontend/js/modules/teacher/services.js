var resourceServices = angular.module('resourceServices', ['ngResource']);

resourceServices.factory('Resource', ['$resource',
    function($resource){

        return $resource(

            BASE_CONFIG.urls.api.resources+':id',
            { 'id': '@id'},
            {
                 update: {method: 'PUT', headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}}
                ,save: {method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}}
            }
        );

    }]);

resourceServices.factory('ResourceDuplication', ['$resource',
    function($resource){

        return $resource(
            BASE_CONFIG.urls.api.resources+':resourceId/duplicate',
            { 'resourceId': '@resourceId'},
            {
                save: {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}
                }
            }
        );

    }]);

var modelServices = angular.module('modelServices', ['ngResource']);

modelServices.factory('Model', ['$resource',
    function($resource){

        return $resource(
            BASE_CONFIG.urls.api.models+':id', //'http://claroline/app_dev.php/claire_exercise/api/exercise-models/:id',
            {},
            {
                'update': {method: 'PUT', params: {'id': '@id'}, headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}}
                ,save: {method: 'POST', isArray: false, headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}}
            }
        );

    }]);

modelServices.factory('ModelDuplication', ['$resource',
    function ($resource) {

        return $resource(
            BASE_CONFIG.urls.api.models + ':modelId/duplicate',
            {'modelId': '@modelId'},
            {
                save: {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}
                }
            }
        );

    }]);
