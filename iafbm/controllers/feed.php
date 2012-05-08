<?php

class FeedController extends iaWebController {

    function defaultAction() {
        // Fetches latest events
        $versions = xModel::load('version', array(
            'xlimit' => 20,
            'xorder_by' => 'created',
            'xorder' => 'DESC'
        ))->get();
        // Fetches events related data
        $events = array();
        foreach ($versions as $version) {
            $event = array();
            // Adds version data to event
            $event['version'] = $version;
            // Adds version deltas data to event
            $event['deltas'] = xModel::load('version_data', array(
                'version_id' => $version['id'],
                'xjoin' => ''
            ))->get();
            // Adds related entity data to event
            $event['entity'] = xModel::load($version['model_name'], array(
                $version['id_field_name'] => $version['id_field_value'],
                'actif' => array(0,1)
            ))->get(0);
            // Adds related foreign entity data to event (if applicable)
            /*
            $foreign_mapping = xModel::load($version['model_name'])->foreign_mapping();
            foreach ($event['deltas'] as $delta) {
                if (in_array($delta['field_name'], $foreign_mapping)) {
                    $model = ...;
                    $event['foreigns'][$model] = xModel::load($model, y)->get();
                }
            }
            */
            $events[] = $event;
        }
        // Renders an HTML line per event
        $lines = array();
        foreach ($events as $event) {
            $model = $event['version']['model_name'];
            $operation = $event['version']['operation'];
            try {
                $lines[] = array(
                    'event' => xView::load("feed/events/{$model}/{$operation}", $event)->render(),
                    'info' => xView::load('feed/info', $event)->render(),
                    // TODO:
                    //'delta' => xView::load('feed/delta', $event)->render()
                );
            } catch (xException $e) {
                if ($e->status == 404) null;
                else throw $e;
            }
        }
        // Renders an HTML event list
        return xView::load('feed/events', $lines)->render();
    }
}