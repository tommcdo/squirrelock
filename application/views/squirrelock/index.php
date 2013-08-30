<html>
	<head>
		<style type="text/css">
			li.link {
				display: inline-block;
				width: 100px;
				padding: 3px 0;
			}
			td, th {
				padding: 0 3px;
				border: 1px solid #ddd;
				border-top: 0;
				border-left: 0;
			}
			th {
				text-align: right;
			}
		</style>
	</head>
	<body>
		<h1><?php echo $table; ?> (<?php echo $pk; ?>)</h1>
		<h2>Record details</h2>
		<table>
			<?php foreach ($details as $property => $value): ?>
				<tr>
					<th><?php echo $property; ?></th>
					<td><?php echo $value; ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
		<h2>Inbound references</h2>
		<ul>
			<?php foreach ($references as $reference => $pks): ?>
				<li>
					<h3><?php echo $reference; ?></h3>
					<ul>
						<?php foreach ($pks as $pk): ?>
							<li class="link"><a href="<?php echo Route::url('squirrelock', array('table' => Arr::get(explode('.', $reference), 0), 'pk' => $pk)); ?>"><?php echo $pk; ?></a></li>
						<?php endforeach; ?>
					</ul>
				</li>
			<?php endforeach; ?>
		</ul>
		<h2>Other <?php echo $table; ?> records</h2>
		<ul>
			<?php foreach ($primary_keys as $primary_key): ?>
				<li class="link"><a href="<?php echo Route::url('squirrelock', array('table' => $table, 'pk' => $primary_key)); ?>"><?php echo $primary_key; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</body>
</html>
