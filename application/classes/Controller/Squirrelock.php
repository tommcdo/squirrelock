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

		$twig = Twig::factory('squirrelock/index');
		$twig->table = $table;
		$twig->pk = $pk;
		$twig->details = $details;
		$twig->references = $references;
		$twig->primary_keys = $primary_keys;

		$this->response->body($twig);
	}

} // End Squirrelock
