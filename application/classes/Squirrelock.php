<?php defined('SYSPATH') or die('No direct script access.');

class Squirrelock {

	/**
	 * The database name we are connecting to.
	 */
	protected $_table_schema = NULL;

	/**
	 * Table this instance will be dealing with.
	 */
	protected $_table;

	/**
	 * Primary key column name for each table.
	 */
	protected $_primary_key = array();

	/**
	 * All tables with in-bound foreign keys to this table.
	 */
	protected $_referencing_tables = NULL;

	/**
	 * Detailed information about various tables.
	 */
	protected $_table_detail = array();

	/**
	 * Constructor.
	 */
	public function __construct($table)
	{
		if ($this->_table_schema === NULL)
		{
			$this->_table_schema = Kohana::$config->load('database.default.connection.database');
		}
		$this->_table = $table;
	}

	/**
	 * Get the primary key column name for this table.
	 */
	public function primary_key($table = NULL)
	{
		if ($table === NULL)
		{
			$table = $this->_table;
		}

		if ( ! array_key_exists($table, $this->_primary_key))
		{
			$this->_primary_key[$table] = Arr::get($this->_table_detail($table), 'primary_key');
		}

		return $this->_primary_key[$table];
	}

	/**
	 * Get an array of existing primary key values within this table.
	 */
	public function primary_keys()
	{
		$primary_key = $this->primary_key();
		$primary_keys = DB::select($primary_key)
			->from($this->_table)
			->execute()
			->as_array(NULL, $primary_key);

		return $primary_keys;
	}

	/**
	 * Get record details for a particular primary key.
	 */
	public function details($pk)
	{
		$record = DB::select('*')
			->from($this->_table)
			->where($this->primary_key(), '=', $pk)
			->execute();

		return $record[0];
	}

	/**
	 * Find a list of tables with an in-bound foreign key to this table.
	 */
	public function referencing_tables()
	{
		if ($this->_referencing_tables === NULL)
		{
			$this->_referencing_tables = DB::select(array('TABLE_NAME', 'table'), array('COLUMN_NAME', 'column'))
				->from('INFORMATION_SCHEMA.KEY_COLUMN_USAGE')
				->where('TABLE_SCHEMA', '=', $this->_table_schema)
				->where('REFERENCED_TABLE_NAME', '=', $this->_table)
				->execute()
				->as_array();
		}
		return $this->_referencing_tables;
	}

	/**
	 * Get actual records referencing this table.
	 */
	public function inbound_references($pk)
	{
		$references = array();
		foreach ($this->referencing_tables() as $referencing_table)
		{
			$table = $referencing_table['table'];
			$column = $referencing_table['column'];
			$table_detail = $this->_table_detail($table);
			if ( ! $table_detail['pivot'])
			{
				$primary_key = $this->primary_key($table);
				$refs = DB::select($primary_key)
					->from($table)
					->where($column, '=', $pk)
					->execute()
					->as_array(NULL, $primary_key);
				if (count($refs) > 0)
				{
					$references["$table.$column"] = array(
						'table'      => $table,
						'column'     => $column,
						'references' => $refs,
					);
				}
			}
			else
			{
				foreach ($table_detail['foreign_keys'] as $foreign_key => $reference)
				{
					$ref_table = $reference['table'];
					$ref_column = $reference['column'];
					if ($ref_table === $this->_table AND $ref_column === $this->primary_key())
						continue;

					$refs = DB::select($foreign_key)
						->from($table)
						->where($column, '=', $pk)
						->execute()
						->as_array(NULL, $foreign_key);
					if (count($refs) > 0)
					{
						$references["$ref_table.$ref_column ($table.$column)"] = array(
							'table'      => $ref_table,
							'column'     => $ref_column,
							'references' => $refs,
						);
					}
				}
			}
		}
		return $references;
	}

	/**
	 * Get detailed information about a table.
	 */
	protected function _table_detail($table)
	{
		if ( ! array_key_exists($table, $this->_table_detail))
		{
			$primary_key = DB::select('COLUMN_NAME')
				->from('INFORMATION_SCHEMA.KEY_COLUMN_USAGE')
				->where('TABLE_SCHEMA', '=', $this->_table_schema)
				->where('TABLE_NAME', '=', $table)
				->where('CONSTRAINT_NAME', '=', 'PRIMARY')
				->execute()
				->as_array(NULL, 'COLUMN_NAME');

			$foreign_keys_result = DB::select('COLUMN_NAME', 'REFERENCED_TABLE_NAME', 'REFERENCED_COLUMN_NAME')
				->from('INFORMATION_SCHEMA.KEY_COLUMN_USAGE')
				->where('TABLE_SCHEMA', '=', $this->_table_schema)
				->where('TABLE_NAME', '=', $table)
				->where('REFERENCED_TABLE_NAME', '!=', NULL)
				->execute();
			$foreign_keys = array();
			foreach ($foreign_keys_result as $row)
			{
				$foreign_keys[$row['COLUMN_NAME']] = array(
					'table'  => $row['REFERENCED_TABLE_NAME'],
					'column' => $row['REFERENCED_COLUMN_NAME'],
				);
			}
			$pivot = count(array_intersect($primary_key, array_keys($foreign_keys))) > 0;
			if (count($primary_key) === 1)
			{
				$primary_key = $primary_key[0];
			}

			$this->_table_detail[$table] = array(
				'primary_key'  => $primary_key,
				'foreign_keys' => $foreign_keys,
				'pivot'        => $pivot,
			);
		}

		return $this->_table_detail[$table];
	}

} // End Squirrelock
