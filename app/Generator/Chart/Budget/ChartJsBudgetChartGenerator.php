<?php

namespace FireflyIII\Generator\Chart\Budget;


use Config;
use Illuminate\Support\Collection;
use Preferences;

/**
 * Class ChartJsBudgetChartGenerator
 *
 * @package FireflyIII\Generator\Chart\Budget
 */
class ChartJsBudgetChartGenerator implements BudgetChartGenerator
{

    /**
     * @param Collection $entries
     * @param string     $dateFormat
     *
     * @return array
     */
    public function budget(Collection $entries, $dateFormat = 'month')
    {
        // language:
        $language = Preferences::get('language', 'en')->data;
        $format   = Config::get('firefly.' . $dateFormat . '.' . $language);

        $data = [
            'labels'   => [],
            'datasets' => [
                [
                    'label' => 'Amount',
                    'data'  => [],
                ]
            ],
        ];

        /** @var array $entry */
        foreach ($entries as $entry) {
            $data['labels'][]              = $entry[0]->formatLocalized($format);
            $data['datasets'][0]['data'][] = $entry[1];

        }

        $data['count'] = count($data['datasets']);

        return $data;
    }

    /**
     * @codeCoverageIgnore
     *
     * @param Collection $entries
     *
     * @return array
     */
    public function budgetLimit(Collection $entries)
    {
        return $this->budget($entries, 'monthAndDay');
    }

    /**
     * @param Collection $entries
     *
     * @return array
     */
    public function frontpage(Collection $entries)
    {
        $data = [
            'count'    => 0,
            'labels'   => [],
            'datasets' => [],
        ];
        // dataset: left
        // dataset: spent
        // dataset: overspent
        $left      = [];
        $spent     = [];
        $overspent = [];
        foreach ($entries as $entry) {
            if ($entry[1] != 0 || $entry[2] != 0 || $entry[3] != 0) {
                $data['labels'][] = $entry[0];
                $left[]           = round($entry[1], 2);
                $spent[]          = round($entry[2], 2);
                $overspent[]      = round($entry[3], 2);
            }
        }

        $data['datasets'][] = [
            'label' => trans('firefly.left'),
            'data'  => $left,
        ];
        $data['datasets'][] = [
            'label' => trans('firefly.spent'),
            'data'  => $spent,
        ];
        $data['datasets'][] = [
            'label' => trans('firefly.overspent'),
            'data'  => $overspent,
        ];

        $data['count'] = count($data['datasets']);

        return $data;
    }

    /**
     * @param Collection $budgets
     * @param Collection $entries
     *
     * @return array
     */
    public function year(Collection $budgets, Collection $entries)
    {
        // language:
        $language = Preferences::get('language', 'en')->data;
        $format   = Config::get('firefly.month.' . $language);

        $data = [
            'labels'   => [],
            'datasets' => [],
        ];

        foreach ($budgets as $budget) {
            $data['labels'][] = $budget->name;
        }
        /** @var array $entry */
        foreach ($entries as $entry) {
            $array = [
                'label' => $entry[0]->formatLocalized($format),
                'data'  => [],
            ];
            array_shift($entry);
            $array['data']      = $entry;
            $data['datasets'][] = $array;

        }
        $data['count'] = count($data['datasets']);

        return $data;
    }

    /**
     * @param Collection $entries
     *
     * @return array
     */
    public function multiYear(Collection $entries)
    {
        //var_dump($entries);
        $data = [
            'count'    => 0,
            'labels'   => [],
            'datasets' => [],
        ];
        // labels: for each budget.
        // dataset: for each year?
        foreach($entries as $entry) {
            $year = $entry['date']->year;
            if(!in_array($year, $data['labels'])) {
                $data['labels'][] = $entry['date']->year;
            }
        }
        // can be joined?
        $set = [];
        foreach($entries as $entry) {
            $name = $entry['budget'];
            $set[$name] = isset($set[$name]) ? $set[$name] : [];
            $set[$name][] = ($entry['sum'] * -1);
        }
        foreach($set as $name => $values) {
            $data['datasets'][] = ['label' => $name, 'data' => $values];
        }
        $data['count'] = count($data['datasets']);

return $data;
        //var_dump($data);
        //exit;

    }
}
