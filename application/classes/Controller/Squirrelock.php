<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Squirrelock extends Controller {

	public function action_index()
	{
		$table = $this->request->param('table');
		$pk = $this->request->param('pk');

		$squirrelock = new Squirrelock($table);
		$primary_keys = $squirrelock->primary_keys();
		$references = $squirrelock->inbound_references($pk);
		$details = $squirrelock->details($pk);

		$view = View::factory('squirrelock/index');
		$view->table = $table;
		$view->pk = $pk;
		$view->details = $details;
		$view->references = $references;
		$view->primary_keys = $primary_keys;

		$this->response->body($view);
	}

} // End Squirrelock
