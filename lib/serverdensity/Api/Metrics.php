<?php

namespace serverdensity\Api;

class Metrics extends AbstractApi
{
    private $data;

    /**
    * Get available metrics
    * @link     https://developer.serverdensity.com/docs/available-metrics
    * @param    string      $id     the subjectID to get available metrics
    * @param    timestamp   $start  the start of the period.
    * @param    timestamp   $end    the end of the period
    * @return   an array that is all available metrics.
    */
    public function available($id, $start, $end){
        $param = array(
            'start' => date("Y-m-d\TH:i:s\Z", $start),
            'end' => date("Y-m-d\TH:i:s\Z", $end)
        );

        return $this->get('metrics/definitions/'.urlencode($id), $param);
    }


    /**
    * Get actual metrics
    * @link     https://developer.serverdensity.com/docs/get-metrics
    * @param    string      $id     the subjectID to get available metrics
    * @param    array       $filter an array of what you want to filter
    * @param    timestamp   $start  the start of the period.
    * @param    timestamp   $end    the end of the period
    * @return   an array that is all available metrics.
    */
    public function metrics($id, $filter, $start, $end){
        $param = array(
            'start' => date("Y-m-d\TH:i:s\Z", $start),
            'end' => date("Y-m-d\TH:i:s\Z", $end),
            'filter' => $filter
        );

        $param = $this->makeJsonReady($param);

        return $this->get('metrics/graphs/'.urlencode($id), $param);
    }

    /**
    * Get dynamic metrics
    * @link     https://developer.serverdensity.com/docs/dynamic-metrics
    * @param    array       $filter an array of what you want to filter
    * @param    timestamp   $start  the start of the period.
    * @param    timestamp   $end    the end of the period
    * @param    string      $inventoryFilter the filter to use to find inventory
    * @param    array       $ids an array of ids that you want to filter for.
    * @param    array       $names an array of names that you would like to filter for.
    * @return   an array that is all available metrics.
    */
    public function dynamicMetrics($filter, $start, $end, $inventoryFilter=Null){
        $urlencoded = '';
        $query = array();
        if (isset($inventoryFilter)){
            $query['inventoryFilter'] = $inventoryFilter;
        }
        if (!empty($query)){
            $urlencoded = '?' . http_build_query($query);
        }

        $param = array(
            'start' => date("Y-m-d\TH:i:s\Z", $start),
            'end' => date("Y-m-d\TH:i:s\Z", $end),
            'filter' => $filter
        );

        $param = $this->makeJsonReady($param);

        return $this->get('metrics/dynamicgraphs/' . $urlencoded, $param);
    }

    private function collectData($tree){
        foreach($tree as $tr){
            if (key_exists('data', $tr)) {
                $this->data[] = $tr;
            } else {
                if (!key_exists('source', $tr)){
                    $tr['source'] = $tr['name'];
                }
                foreach($tr['tree'] as $key => $val){
                    $tr['tree'][$key]['source'] = $tr['source']." > ".$val['name'];
                }
                $this->collectData($tr['tree']);
            }
        }
    }

    public function separateXYdata($data){
        foreach($data as $key => $graph){
            $xPoints = array();
            $yPoints = array();
            foreach($graph['data'] as $point){
                $xPoints[] = $point['x'];
                $yPoints[] = $point['y'];
            }
            $data[$key]['xPoints'] = $xPoints;
            $data[$key]['yPoints'] = $yPoints;
        }
        unset($data['data']);
        return $data;
    }

    public function formatMetrics($data)
    {
        $this->data = array();
        $this->collectData($data);
        return $this->data;
    }

}
