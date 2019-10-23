<?php


namespace App\Extensions\Dag;


use App\Beans\DagPipeline;
use Fhaculty\Graph\Graph;
use Illuminate\Pipeline\Pipeline;

class DagManager
{
    private $graph_manager = [];

    public function __construct($config)
    {
        foreach ($config as $task_name => $task_config) {
            $graph = new Graph();
            $task = $graph->createVertex($task_name);
            array_walk($task_config['deps'], [$this, 'parseDeps'], [$graph, $task]);
            $this->graph_manager[$task_name] = $task;
        };
    }

    /**
     * @param $deps_config
     * @param $dep_task_name
     * @param $params
     * @return bool
     */
    private function parseDeps($deps_config, $dep_task_name, $params)
    {
        /**
         * @var $graph Graph
         */
        [$graph, $parent_task] = $params;
        if (!isset($deps_config['class'])) {
            return false;
        }
        $dep_task = $graph->createVertex($dep_task_name);
        $dep_task->setAttribute('class_name', $deps_config['class']);
        $dep_task->createEdgeTo($parent_task);

        if (isset($deps_config['deps'])) {
            foreach ($deps_config['deps'] as $key => $value) {
                $this->parseDeps($value, $key, [$graph, $dep_task]);
            }
        }
    }


    /**
     * @param string $task_name
     * @return \Illuminate\Support\Collection
     */
    public function getPipes(string $task_name)
    {
        return collect($this->graph_manager[$task_name]->getVerticesEdgeFrom())->reduce(function ($carry, $vertex) {
            /**
             * @var $vertex \Fhaculty\Graph\Vertex
             */
            if ($vertex->getVerticesEdgeFrom()->count() > 0) {
                $class_names = $this->parsePipeLines($vertex);
                array_push($carry, ...$class_names);
            }

            array_push($carry, $vertex->getAttribute('class_name'));
            return $carry;
        }, []);
    }

    /**
     * @param \Fhaculty\Graph\Vertex $vertex
     * @return array
     */
    private function parsePipeLines(\Fhaculty\Graph\Vertex $vertex)
    {
        $pipes = [];
        foreach ($vertex->getVerticesEdgeFrom() as $sub_vertex) {
            /**
             * @var $sub_vertex \Fhaculty\Graph\Vertex
             */
            if ($sub_vertex->getVerticesEdgeFrom()->count() > 0) {
                $pipes += $this->parsePipeLines($sub_vertex);
            }

            array_push($pipes, $sub_vertex->getAttribute('class_name'));
        };
        return $pipes;
    }

    /**
     * @param string $task_name
     * @param DagPipeline $dagPipeline
     * @return Pipeline
     */
    public function pipeline(string $task_name, DagPipeline $dagPipeline)
    {
        $pipes = $this->getPipes($task_name);

        /**
         * @var $pipeline Pipeline
         */
        $pipeline = app(Pipeline::class);

        return $pipeline->send($dagPipeline)->through($pipes);
    }
}