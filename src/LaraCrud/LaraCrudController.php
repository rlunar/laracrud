<?php

namespace LaraCrud;

use Event;
use ReflectionClass;
use Illuminate\Http\Request;
use Illuminate\Contracts\Validation\Validator;

trait LaraCrudController
{
    /**
     * Flash a message when validate method fails
     * 
     * @param  Validator $validator
     * @return array
     */
    protected function formatValidationErrors(Validator $validator)
    {
        flash()->error('Oops!', 'Something went wrong!');
        return $validator->errors()->all();
    }

    /**
     * Load index view with table data, including header table and rows
     * 
     * @return array
     */
    public function index()
    {
        $crudData = $this->getCrudData();
        $table    = [
            'headers'     => $this->model->getHeaders(),
            'results'     => LaraCrud::displayForeignLinks($this->model),
            'displayable' => $this->displayable
        ];

        $this->triggerEvent('index');
        
        if (isset($this->views)) {
            return view($this->views . '.index', compact('table', 'crudData'));
        } else {
            return view('lara_crud::index', compact('table', 'crudData'));
        }
    }

    /**
     * Load create view with inputs dinamically generated
     * 
     * @return array
     */
    public function create()
    {
        $crudData = $this->getCrudData();
        $inputs   = LaraCrud::getInputs($this->model);
        $rules    = $this->model->getRules();

        $this->triggerEvent('create');

        if (isset($this->views)) {
            return view($this->views . '.create', compact('inputs', 'crudData', 'rules'));
        } else {
            return view('lara_crud::create', compact('inputs', 'crudData', 'rules'));
        }
    }

    /**
     * Store data into DB
     * 
     * @param  Illuminate\Http\Request $request
     * @return Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->model->getRules());
        
        $result = new $this->model($request->all());

        $result->save();

        flash()->success('Great!', 'A new '.$this->crudName.' has been created');

        $this->triggerEvent('store', $request, $result);
        
        return redirect($this->route);
    }

    /**
     * Show row info, generate inputs dinamically
     * 
     * @param  integer $id
     * @return Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function show($id)
    {
        $crudData = $this->getCrudData();
        $record   = $this->model->findOrFail($id);
        $inputs   = LaraCrud::getInputs($this->model, $record, true);

        $this->triggerEvent('show');

        if (isset($this->views)) {
            return view($this->views . '.show', compact('record', 'inputs', 'crudData'));
        } else {
            return view('lara_crud::show', compact('record', 'inputs', 'crudData'));
        }
    }

    /**
     * Show edit form with inputs dinamically generated
     * 
     * @param  integer $id
     * @return Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {
        $crudData = $this->getCrudData();
        $record   = $this->model->findOrFail($id);
        $inputs   = LaraCrud::getInputs($this->model, $record);
        $rules    = $this->model->getRules();

        $this->triggerEvent('edit');

        if (isset($this->views)) {
            return view($this->views . '.edit', compact('record', 'inputs', 'crudData', 'rules'));
        } else {
            return view('lara_crud::edit', compact('record', 'inputs', 'crudData', 'rules'));
        }
    }

    /**
     * Update row data
     * 
     * @param  Illuminate\Http\Request $request
     * @param  integer                 $id
     * @return Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, $this->model->getRules());

        $result = $this->model->findOrFail($id)->update($request->all());

        flash()->success('Great!', 'The '.$this->crudName.' has been updated');

        $this->triggerEvent('update', $request, $result);
        
        return redirect($this->route.'/'.$id);
    }

    /**
     * Delete row from DB
     * 
     * @param  integer $id
     * @return Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $result = $this->model->findOrFail($id)->delete();

        flash()->success('Great!', 'The '.$this->crudName.' has been deleted');

        $this->triggerEvent('destroy', null, $result);

        return redirect($this->route);
    }

    /**
     * Get Dropdowns
     * 
     * @return json
     */
    public function getDropdowns()
    {
        return response()->json(LaraCrud::getForeignDataDropDown());
    }

    /**
     * Get Crud Data
     * 
     * @return array
     */
    private function getCrudData()
    {
        $class = explode("\\", get_class());

        return [
            'crudName'       => $this->crudName,
            'crudRoute'      => $this->route,
            'crudController' => end($class)
        ];
    }

    /**
     * Trigger Event if defined on events property
     * 
     * @param  string $event
     * @return void
     */
    private function triggerEvent($event, Request $request, $result)
    {
        if (in_array($event, array_keys($this->events))) {
            $event       = $this->events[$event];
            $class       = new ReflectionClass($event['class']);
            $params      = [];
            $constructor = $class->getConstructor();
            $numParams   = sizeof($constructor->getParameters());
            
            for ($i = 0; $i < $numParams; $i++) {
                if (strpos($event['params'][$i], ':')) {
                    $param = explode(':', $event['params'][$i]);
                    $params[] = ${$param[0]}->{$param[1]};
                } else {
                    $params[] = ${$event['params'][$i]};
                }
            }
            $instance = $class->newInstanceArgs($params);
            Event::fire($instance);
        }
    }
}
