<?php
// GLOBAL VARIABLES
include "config.php";;
global $service_time;

session_name($appname);
session_start();
authenticate();

// if the person logged in using our public registration page,
// but then try visiting any other page, they get redirected to the public registration page again.
// the only pages they are allowed to view are pre-register.php, ajax.php, and household.php
// -- ajax and household do their own checks to make sure family information is not made public.
if ( $_SESSION['logged_in'] === 'public'
	and strpos($_SERVER['REQUEST_URI'], 'pre-register') === false
	and strpos($_SERVER['REQUEST_URI'], 'ajax.php') === false
	and strpos($_SERVER['REQUEST_URI'], 'household.php') === false)
{
	//logout();
	Header("Location: pre-register.php");
}

if (isset($_SESSION['timeout']) and $_SESSION['timeout'] + 2 * 60 * 60 < time())
{
    session_unset();
    session_destroy();
    session_write_close();
    setcookie(session_name(),'',0,'/');
    session_regenerate_id(true);
    unset($_COOKIE);

	session_name($appname);
	session_start();
	while (! isset($_SESSION['logged_in']) and ! isset($_COOKIE[$cookiename]) and ! isset($_GET['login_override'])) authenticate();
}
$_SESSION['timeout'] = time();


// handle session details for service time changes
date_default_timezone_set('America/New_York');
//die(strftime("%m/%d/%Y %H:%M:%s", time()));

if ( isset($_GET['service_time']) )
{
	if ($_GET['service_time'] == 'default')
	{
		unset($service_time);
		unset($_SESSION['service_time']);
	}
	else
	{
		$service_time = $_GET['service_time'];
		$_SESSION['service_time'] = $service_time;
	}
}

global $is_testing;
if (isset($_GET['testing'])) $is_testing = TRUE;
else $is_testing = FALSE;


global $is_admin;
if (isset($_GET['admin'])) $is_admin = TRUE;
else $is_admin = FALSE;


global $err;
global $msg;
global $today;
global $today_timestamp;


$err = 0;
$msg = '';


// CODE TO SET UP SERVICE TIMES, LABELS AND TIMESTAMPS

// WE MAKE THE TODAY TIMESTAMP START AT 6AM BECAUSE ON
// DAYS WHEN DAYLIGHT SAVINGS TIME STARTS, THE CODE TO COMPUTE
// THE PREVIOUS SUNDAY CAN FAIL
$today_timestamp = strtotime(strftime("%m/%d/%Y", time()) . " 06:00");
if ($is_testing) $today_timestamp = 1322985600;

$today = strftime("%m/%d/%Y", $today_timestamp);
$day_of_week = idate("w");
$sunday_timestamp = $today_timestamp - ($day_of_week * 60 * 60 * 24);
$sunday = strftime("%m/%d/%Y", $sunday_timestamp);
//die(strftime("%m/%d/%Y %H:%M", $sunday_timestamp));

$service_times = Array();
foreach ($service_start_times as $sst)
{
	$service_times[$sst['label']] = strtotime($sunday . ' ' . $sst['time']);
}


// Instead of automatically defaulting to the most recent Sunday,
// identify the default service time unless one has been selected manually
if ( isset($_SESSION["service_time"] ) )
{
	$service_time = $_SESSION['service_time'];
	$service_timestamp = $service_times[$service_time];
}
else
{
	foreach ($service_times as $t=>$ts)
	{
		if ( $sunday_timestamp < time()  and time() < ( $ts + 30 * 60 ) )
		{
			$service_timestamp = $ts;
			$service_time = $t;
			break;
		}
		else
		{
			$service_timestamp = $ts;
			$service_time = $t;
		}
	}
}

$service = strftime("%m/%d/%Y %H:%M", $service_timestamp);

$notifications = 'ON';
$time_diff = abs($service_timestamp - time());
if ($time_diff > 120 * 60) $notifications = 'OFF';




// CLEAR SESSION VARIABLES
if (isset($_SESSION['err']))
{
	$err = $_SESSION['err'];
	unset($_SESSION['err']);
}
if (isset($_SESSION['msg']))
{
	$msg = $_SESSION['msg'];
	unset($_SESSION['msg']);
}


class my_db_mysql extends mysqli
{
	function __construct()
	{
		global $db_host;
		global $db_name;
		global $db_user;
		global $db_pass;
		//parent::__construct($this->$host, $this->$user, $this->$password, $this->$database);
		parent::__construct($db_host, $db_user, $db_pass, $db_name);
	}
	function fakequery($s)
	{
		debug($s);
	}
	function get_rows($sql)
	{
		$retval = Array();
		if ($this->real_query($sql))
		{
			if ($result = $this->use_result())
			{
				while ($row = $result->fetch_assoc())
				{
					$retval[] = $row;
				}
				$result->close();
			}
		}
		return $retval;
	}
	public function escapeString($s)
	{
		return $this->real_escape_string($s);
	}
	public function lastInsertRowID()
	{
		return $this->insert_id;
	}
}

/* FUNCTIONS */
function debug ($s)
{
	global $showdebug;
	if ($showdebug)
	{
		print "\n<pre>\n";
		print_r ($s);
		print "\n</pre>\n";
	}
}
function today()
{
	return strtotime(strftime("%m/%d/%Y", time()));
}

function do_install($dump = FALSE)
{
	global $db;
	if ($dump)
	{
		$db->exec('DROP TABLE households');
		$db->exec('DROP TABLE children');
		$db->exec('DROP TABLE attendance');
		$db->exec('DROP TABLE household2child');
		$db->exec('DROP TABLE notes');
	}
	$db->exec(
		'CREATE TABLE households (
			id integer PRIMARY KEY AUTOINCREMENT,
			household_name text,
			home_phone text,
			email text,
			cell_phone text,
			address text,
			city text,
			state text,
			zip text,
			date date);'
		);
	$db->exec(
		'CREATE TABLE children (
			id integer PRIMARY KEY AUTOINCREMENT,
			first_name text,
			last_name text,
			birthday date,
			allergies longtext,
			status text);'
		);
	$db->exec(
		'CREATE TABLE attendance (
			id integer PRIMARY KEY AUTOINCREMENT,
			child_id integer,
			date date,
			room_id integer,
			note longtext);'
		);
	$db->exec(
		'CREATE TABLE household2child (
			id integer PRIMARY KEY AUTOINCREMENT,
			household_id integer,
			child_id integer);'
		);
	$db->exec(
		'CREATE TABLE notes (
			id integer PRIMARY KEY AUTOINCREMENT,
			household_id integer,
			note longtext,
			date date);'
		);

}

function my_query($sql)
{
	global $db;
	$rows = $db->get_rows($sql);
	return $rows;
	/*
	$retval = Array();
	while ($row = $result->fetchArray(SQLITE3_ASSOC))
	{
		$retval[] = $row;
	}
	return $retval;
	*/
}




/*
function get_households($household_id = '')
{
	global $db;
	if ($household_id)
	{
		$sql = sprintf("SELECT * FROM households WHERE id='%s'", $db->escapeString($household_id));
		$return_single = TRUE;
	}
	else
	{
		$sql = sprintf("SELECT * FROM households");
		$return_single = FALSE;
	}

	$result = $db->query($sql);
	if ($result === FALSE) return 0;
	$retval = Array();
	while ($row = $result->fetchArray()) $retval[] = $row;
	return $retval;
}
*/

function get_rooms($simple_array = FALSE)
{
	global $rooms;
	if ( ! $simple_array ) return $rooms;
	$retval = Array();
	foreach ($rooms as $room)
	{
		$retval[] = $room['name'];
	}
	return $retval;
}

function get_notes($household_id)
{
	global $db;
	$sql = sprintf("SELECT * FROM notes WHERE household_id='%s' ORDER BY date DESC", $db->escapeString($household_id));
	$result = $db->query($sql);
	$retval = Array();
	//while ($row = $result->fetchArray(SQLITE3_ASSOC))
	while ($row = $result->fetch_assoc())
	{
		$row['date'] = strftime("%m/%d/%Y %I:%M %P", $row['date']);
		$retval[] = $row;
	}
	return $retval;
}

function get_checkins($date = '')
{
	global $db;
	global $service_timestamp;
	// get list of all checkins for date default to today
	if ($date == '')
	{
		//$date = strtotime(strftime("%m/%d/%Y", time()));
		$date = $service_timestamp;
	}
	$sql = sprintf("SELECT * FROM attendance WHERE date='%s'", $db->escapeString($date));
	$result = $db->query($sql);
	$retval = Array();
	//while ($row = $result->fetchArray(SQLITE3_ASSOC))
	while ($row = $result->fetch_assoc())
	{
		$child = get_children($row['child_id']);
		$retval['by_children'][$row['child_id']] = $child;
		$retval['by_children'][$row['child_id']]['date'] = $row['date'];
		$retval['by_room'][$row['room_id']][$row['child_id']] = $child;
		$retval['by_room'][$row['room_id']][$row['child_id']]['date'] = $child;
	}
	return $retval;
}

function get_allergies($child_id = '')
{
	global $db;
	if ($child_id === '')
	{
		$sql = 'SELECT * FROM allergy_list';
	}
	else
	{
		$sql = sprintf("SELECT * FROM allergy_list WHERE id IN (SELECT allergy_id FROM child2allergy ca WHERE ca.child_id = '%s')", $db->escapeString($child_id));
	}
	$allergies = my_query($sql);
	$retval = Array();
	foreach ($allergies as $allergy)
	{
		$id = $allergy['id'];
		$label = $allergy['label'];
		$retval[$id] = $label;
	}
	return $retval;
}

function save_allergies($child_id, $allergy_array)
{
	global $db;
	//debug('saving allergy data');
	//debug($allergy_array);

	// step one: clear out current allergy information for this child from database
	$sql = sprintf("DELETE FROM child2allergy WHERE child_id = '%s'", $db->escapeString($child_id));
	//debug($sql);
	my_query($sql);

	// step two: insert an association for each allergy
	foreach ($allergy_array as $allergy_id=>$allergy_name)
	{
		$sql = sprintf("INSERT INTO child2allergy (child_id, allergy_id) VALUES ('%s', '%s')", $db->escapeString($child_id), $db->escapeString($allergy_id) );
		//debug($sql);
		my_query($sql);
	}
}


function save_note($note, $household)
{

	global $db;
	$household_id = $household['id'];

	// if id is set, we delete or update
	if ( isset( $note['id'] ) )
	{
		// if note body is empty, delete it, otherwise update it.
		if ( $note['note'] == '' )
		{
			$sql = sprintf("DELETE FROM notes WHERE id='%s'", $db->escapeString($note['id']));
			$result = $db->query($sql);
		}
		else
		{
			// get old note to see if it has been changed
			$sql = sprintf("SELECT * FROM notes WHERE id='%s'", $db->escapeString($note['id']));
			$result = $db->query($sql);
			//$row = $result->fetchArray(SQLITE3_ASSOC);
			$row = $result->fetch_assoc();
			$old_note = $row['note'];
			$new_note = $note['note'];
			if ( $old_note == $new_note ) return;

			// note has been changed, update it
			$sql = sprintf("UPDATE notes SET date='%s', note='%s' WHERE id='%s'", time(), $db->escapeString($note['note']), $db->escapeString($note['id']));
			$result = $db->query($sql);
		}
	}
	elseif ($note['note'])
	{
		$sql = sprintf("INSERT INTO notes (date, note, household_id) VALUES ('%s', '%s', '%s')", time(), $db->escapeString($note['note']), $db->escapeString($household_id));
		$result = $db->query($sql);
	}
}

function save_household($household)
{
	global $db;
	//$keys = explode('|', 'household_name|address|city|state|zip|email|home_phone|cell_phone');

	foreach (array_keys($household) as $key) ${$key} = $household[$key];
	if (! isset($household['civicrm_id'])) $household['civicrm_id'] = '';
	if (! $household['household_name']) return false;

	if ( $household['delete'] == 'YES' || $household['delete'] == 'yes' )
	{
		// load the full household with children
		$hh = get_households($id);
		$children = $hh['children'];
		// now delete all the children
		foreach ($children as $child)
		{
			$child['delete'] = 'yes';
			//debug($child);
			save_child($child, $household);
		}
		$sql = sprintf("DELETE FROM households WHERE id='%s'", $db->escapeString($id));
		$db->query($sql);
		$_SESSION['msg'] = 'the household has been deleted';
		return TRUE;
	}

	// if the household_id is set, then we update otherwise we add
	if (isset($id))
	{
		$sql = sprintf("UPDATE households SET household_name='%s',address='%s',city='%s',state='%s',zip='%s',email='%s',home_phone='%s',cell_phone='%s' WHERE id='%s'",
			$db->escapeString(trim($household_name)),
			$db->escapeString(strtoupper(trim($address))),
			$db->escapeString(strtoupper(trim($city))),
			$db->escapeString(strtoupper(trim($state))),
			$db->escapeString(trim($zip)),
			$db->escapeString(trim($email)),
			$db->escapeString($home_phone),
			$db->escapeString($cell_phone),
			$db->escapeString($id)
		);
		$db->query($sql);
		return TRUE;
	}
	else
	{
		//check to see if household exists
		$sql = sprintf("SELECT * FROM households WHERE household_name='%s'", $household_name);
		$result = $db->query($sql);
		if ($result === FALSE) return 0;

		$sql = sprintf("INSERT INTO households (household_name,address,city,state,zip,email,home_phone,cell_phone,civicrm_id,date) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s','%s', '%s')",
			$db->escapeString(trim($household_name)),
			$db->escapeString(strtoupper(trim($address))),
			$db->escapeString(strtoupper(trim($city))),
			$db->escapeString(strtoupper(trim($state))),
			$db->escapeString(trim($zip)),
			$db->escapeString(trim($email)),
			$db->escapeString($home_phone),
			$db->escapeString($cell_phone),
			$db->escapeString($civicrm_id),
			time()
		);
		$db->query($sql);
		return $db->lastInsertRowID();
	}
}

function save_child($child, $household=NULL)
{
	global $db;
	if ($household == NULL && ! $child['household_id']) return 'error';
	$household_id = $household ? $household['id'] : $child['household_id'];

	if (! isset($child['allergies'])) $child['allergies'] = Array();

	// expand child array to individual variables based on the array key
	foreach (array_keys($child) as $key) ${$key} = $child[$key];
	$birthday = strtotime($birthday) or time();

	// FROM THIS POINT ON, EACH ARRAY KEY HAS BEEN COPIED TO A SEPARATE VARIABLE
	// THEREFORE, WE HAVE $id, $parent_note, and so on.

	if (! isset($child['last_room']) ) $last_room = get_child_room($child);
	if (! isset($child['status']) ) $status = 'active';


	// if child_id is set and greater than zero, do deletes or updates otherwise do inserts
	if (isset($child['id']) AND $child['id'] > 0)
	{
		// WE ARE HERE BECAUSE THIS CHILD ALREADY EXISTS IN THE DATABASE

		$child_id = $child['id'];
		// if the delete flag is set, do a delete
		if ($child['delete'] == 'yes')
		{
			$sql = sprintf("DELETE FROM children WHERE id='%s'", $db->escapeString($child_id));
			$db->query($sql);
			$sql = sprintf("DELETE FROM household2child WHERE child_id='%s'", $db->escapeString($child_id));
			$db->query($sql);
			$sql = sprintf("DELETE FROM child2allergy WHERE child_id='%s'", $db->escapeString($child_id));
			$db->query($sql);
			$sql = sprintf("DELETE FROM attendance WHERE child_id='%s'", $db->escapeString($child_id));
			$db->query($sql);

		}
		else
		{
			$sql = sprintf("UPDATE children SET last_name='%s',first_name='%s',birthday='%s',parent_note='%s',status='%s',last_room='%s' WHERE id='%s'",
				$db->escapeString(trim($last_name)),
				$db->escapeString(trim($first_name)),
				$db->escapeString($birthday),
				$db->escapeString(trim($parent_note)),
				$db->escapeString($status),
				$db->escapeString($last_room),
				$db->escapeString($id)
			);

			//debug($sql);
			$db->query($sql);

			if (isset($allergies)) save_allergies($child_id, $allergies);
		}
	}
	else
	{
		// WE ARE HERE BECAUSE THIS IS A NEW CHILD


		// insert child into children table
		$sql = sprintf("INSERT INTO children (last_name,first_name,birthday,parent_note,status,last_room) VALUES ('%s', '%s', '%s', '%s','%s',%s)",
			$db->escapeString(trim($last_name)),
			$db->escapeString(trim($first_name)),
			$db->escapeString($birthday),
			$db->escapeString($parent_note),
			$db->escapeString($status),
			(int)$last_room
		);
		$db->query($sql);
		$child_id = $db->lastInsertRowID();
		$child['id'] = $child_id;

		if (isset($allergies)) save_allergies($child_id, $allergies);


		// insert relationship into household2child table
		if ($household_id)
		{
			$sql = sprintf("INSERT INTO household2child (household_id, child_id) VALUES ('%s', '%s')", $db->escapeString($household_id), $db->escapeString($child_id));
			$result = $db->query($sql);
			$relationship_id = $db->lastInsertRowID();
			return $relationship_id;
		}
	}
}


function set_child_status($child_id, $status='active')
{
	global $db;
	$sql = sprintf("UPDATE children SET status='%s' WHERE id='%s'",
		$db->escapeString($status),
		$db->escapeString($child_id));
	$db->query($sql);
}


function search_all($args)
{
	global $db;
	$retval = Array();
	//$sql = sprintf("SELECT * FROM children WHERE last_name LIKE '%s' OR first_name LIKE '%s'", $db->escapeString('%' . $args . '%'), $db->escapeString('%' . $args . '%'));
	//$retval['children'] = $db->get_rows($sql);
	$retval['children'] = search_children($args);
	//$sql = sprintf("SELECT * FROM households WHERE household_name LIKE '%s' OR cell_phone LIKE '%s' OR home_phone LIKE '%s' OR email LIKE '%s'", $db->escapeString('%' . $args . '%'), $db->escapeString('%' . $args . '%'), $db->escapeString('%' . $args . '%'), $db->escapeString('%' . $args . '%'));
	//$retval['households'] = $db->get_rows($sql);
	$retval['households'] = search_households($args);
	//$sql = sprintf("SELECT * FROM imported_households WHERE household_name LIKE '%s' OR cell_phone LIKE '%s' OR home_phone LIKE '%s' OR email LIKE '%s'", $db->escapeString('%' . $args . '%'), $db->escapeString('%' . $args . '%'), $db->escapeString('%' . $args . '%'), $db->escapeString('%' . $args . '%'));
	//$retval['imports'] = $db->get_rows($sql);
	$retval['imported_households'] = search_imported_households($args);
	return $retval;
}
function search_households_and_imports($args)
{
	global $db;
	$retval = Array();
	$retval['households'] = search_households($args);
	$retval['imported_households'] = search_imported_households($args);
	return $retval;
}


function search_children($args)
{
	global $db;
	$sql = sprintf("SELECT * FROM children WHERE last_name LIKE '%s' OR first_name LIKE '%s'", $db->escapeString('%' . $args . '%'), $db->escapeString('%' . $args . '%'));
	//$sql = sprintf("SELECT * FROM households, children, household2child WHERE children.last_name LIKE '%s' OR children.first_name LIKE '%s' AND households.id=household2child.household_id AND household2child.child_id=children.id ORDER BY children.last_name ASC;", $db->escapeString('%' . $args . '%'), $db->escapeString('%' . $args . '%'));
	//debug($sql);
	$result = $db->query($sql);
	$retval = Array();
	//while ($row = $result->fetchArray(SQLITE3_ASSOC))
	while ($row = $result->fetch_assoc())
	{
		//$retval[] = $row;
		$retval[] = get_children($row['id']);
	}
	//debug($retval);
	return $retval;
}

function compute_age($child)
{
	if ( ! isset($child['birthday_timestamp']) && ! isset($child['birthday'])) return 0;
	if (! isset($child['birthday_timestamp'])) $child['birthday_timestamp'] = strtotime($child['birthday']);
	$today = new DateTime();
	$today->setTimestamp(time());
	$birthday = new DateTime();
	$birthday->setTimestamp($child['birthday_timestamp']);
	$age = $birthday->diff($today);
	$age = $age->format('%y');
	return $age;
}

function get_children($child_id='all',$status='all')
{
	global $db;
	global $rooms;
	$return_single = FALSE;
	if ($child_id <> 'all')
	{
		$sql = sprintf("SELECT * from households, children, household2child WHERE children.id='%s' AND households.id=household2child.household_id AND household2child.child_id=children.id ORDER BY children.last_name ASC;", $db->escapeString($child_id));
		$return_single = TRUE;
	}
	elseif ($status <> 'all')
	{
		$sql = sprintf("SELECT * from households, children, household2child WHERE households.id=household2child.household_id AND household2child.child_id=children.id AND children.status='%s' ORDER BY children.last_name ASC;", $db->escapeString($status));
	}
	else
		$sql = "SELECT * from households, children, household2child WHERE households.id=household2child.household_id AND household2child.child_id=children.id ORDER BY children.last_name ASC;";
	$result = $db->query($sql);
	$retval = Array();
	//while ($child = $result->fetchArray(SQLITE3_ASSOC))
	while ($child = $result->fetch_assoc())
	{
		$child['id'] = $child['child_id'];

		// reset birthday to display-able date;
		$child['birthday_timestamp'] = $child['birthday'];
		$child['birthday'] = strftime("%m/%d/%Y", $child['birthday']);

		// compute child's age
		$child['age'] = compute_age($child);

		$child['room_id'] = get_child_room($child);
		$child['suggested_room'] = get_suggested_room($child);
		$child['room'] = $rooms[$child['room_id']]['name'];

		$child['allergies'] = get_allergies($child['id']);

		// get child's last attendance date
		$sql = sprintf("SELECT max(date) as last_attended from attendance WHERE attendance.child_id='%s';", $db->escapeString($child['id']));

		$r = $db->query($sql);
		$last_attended = $r->fetch_assoc();
		$child['last_attended'] = $last_attended['last_attended'];
		$retval[] = $child;
	}
	if ($return_single) return $retval[0];
	else return $retval;
}


function get_children_by_room($room_id)
{
	global $db;
	global $rooms;
	$return_single = FALSE;
	$sql = sprintf("SELECT * FROM children WHERE status='active' AND last_room='%s' ORDER BY first_name ASC", $db->escapeString($room_id));
	$children = my_query($sql);
	$retval = Array();
	foreach ($children as $child)
	{
		$retval[] = get_children($child['id']);
	}

	return $retval;

	/*
	print "<hr>";

	$sql = sprintf("
		SELECT *
		FROM children c
		INNER JOIN household2child
			ON c.id = household2child.child_id
		INNER JOIN households
			ON households.id = household2child.household_id
		WHERE c.status='active' AND c.last_room='%s'
		ORDER BY c.first_name ASC",
		$db->escapeString($room_id));

	$result = $db->query($sql);
	$retval = Array();
	while ($child = $result->fetchArray(SQLITE3_ASSOC))
	{
		// compute child's age
		if ( ! $child['birthday'] ) $child['birthday'] = time();
		$today = new DateTime();
		$today->setTimestamp(time());
		$birthday = new DateTime();
		$birthday->setTimestamp($child['birthday']);
		$age = $birthday->diff($today);
		$age = $age->format('%y');
		$child['age'] = $age;

		$child['birthday_timestamp'] = $child['birthday'];

		// reset birthday to display-able date;
		$child['birthday'] = strftime("%m/%d/%Y", $child['birthday']);

		$child['room_id'] = get_child_room($child);
		$child['room'] = $rooms[$child['room_id']]['name'];
		$child['id'] = $child['child_id'];

		$retval[] = $child;
	}
	return $retval;
	*/
}


function get_last_room($child)
{
	global $db;
	$child_id = $child['id'];
	if (! $child_id ) return -1;
	// get most recent room and default to that if no room, guess from age;
	$sql = sprintf("SELECT attendance.room_id FROM attendance WHERE attendance.child_id='%s' ORDER BY attendance.date DESC LIMIT 1", $db->escapeString($child_id));
	$result = $db->query($sql);
	//$row = $result->fetchArray(SQLITE3_ASSOC);
	$row = $result->fetch_assoc();
	if ($row) return $row['room_id'];
	else return -1;
}

function get_child_room($child)
{
	if ( isset($child['id']) )
	{
		$last_room = get_last_room($child);
		if ($last_room !== -1) return $last_room;
	}
	return get_suggested_room($child);
}

function get_suggested_room($child)
{
	global $rooms;

	// compute a suggested room by child age
	if ( ! isset($child['age'])) $child['age'] = compute_age($child);
	$age = $child['age'];
	foreach ($rooms as $room)
	{
		if ($room['min_age'] <= $age and $age <= $room['max_age']) return $room['room_id'];
	}
	return 99;
}

function get_household_only($id)
{
	global $db;
	$sql = sprintf("SELECT * FROM households WHERE id='%s'", $db->escapeString($id));
	$result = $db->query($sql);
	$retval = Array();
	//$row = $result->fetchArray(SQLITE3_ASSOC);
	$row = $result->fetch_assoc();
	return $row;
}

function get_households_and_children($a='')
{
	return get_households($a);
}

function get_households($household_id='')
{
	global $db;
	if ($household_id !== '')
	{
		$sql = sprintf("SELECT * FROM households WHERE households.id='%s'", $db->escapeString($household_id));
	}
	else
	{
		$sql = "SELECT * FROM households";
	}
	$households = my_query($sql);

	$retval = Array();
	foreach ($households as $household)
	{
		$last_attended = 0;
		$sql = sprintf("SELECT children.id FROM children, household2child WHERE household2child.household_id='%s' AND household2child.child_id=children.id;", $db->escapeString($household['id']));
		$children = my_query($sql);

		$all_children = Array();
		foreach($children as $child)
		{
			$all_children[] = get_children($child['id']);
		}
		$household['household_id'] = $household['id'];
		$household['children'] = $all_children;

		foreach($household['children'] as $child)
		{
			$last_attended = max($child['last_attended'], $last_attended);
		}
		$household['last_attended'] = $last_attended;

		$retval[$household['id']] = $household;
	}
	if ($household_id !== '') return $retval[$household_id];
	else return $retval;
}

function search_households($args)
{
	global $db;
	$sql = sprintf("SELECT id FROM households WHERE household_name LIKE '%s' OR cell_phone LIKE '%s' OR home_phone LIKE '%s' OR email LIKE '%s'", $db->escapeString('%' . $args . '%'), $db->escapeString('%' . $args . '%'), $db->escapeString('%' . $args . '%'), $db->escapeString('%' . $args . '%'));
	$result = $db->query($sql);
	$retval = Array();
	//while ($row = $result->fetchArray(SQLITE3_ASSOC))
	while ($row = $result->fetch_assoc())
	{
		$retval[] = get_households($row['id']);
	}
	return $retval;
}

function search_imported_households($args)
{
	global $db;
	$sql = sprintf("SELECT * FROM imported_households WHERE household_name LIKE '%s' OR cell_phone LIKE '%s' OR home_phone LIKE '%s' OR email LIKE '%s'", $db->escapeString('%' . $args . '%'), $db->escapeString('%' . $args . '%'), $db->escapeString('%' . $args . '%'), $db->escapeString('%' . $args . '%'));
	return $db->get_rows($sql);
}

function get_imported_households($id = '')
{
	global $db;
	if ($id) $sql = sprintf("SELECT * FROM imported_households WHERE id='%s'", $db->escapeString($id));
	else $sql = "SELECT * FROM imported_households";
	return $db->get_rows($sql);
}

function get_attendance($child_id = '')
{
	global $db;
	if ($child_id)
	{
		$sql = sprintf("SELECT * FROM attendance, children WHERE attendance.child_id=children.id AND attendance.child_id='%s' ORDER BY attendance.date DESC", $db->escapeString($child_id));
	}
	else
	{
		$sql = "SELECT * FROM attendance, children WHERE attendance.child_id=children.id ORDER BY attendance.date DESC";
	}
	return $db->get_rows($sql);
}


function get_attendance_by_date($date)
{
	global $db;
	$sql = sprintf("SELECT * FROM attendance, children WHERE attendance.child_id=children.id AND attendance.date = '%s' ORDER BY attendance.date DESC", $db->escapeString($date));
	return $db->get_rows($sql);
}

function toggle_attendance($attendance, $notify=false)
{
	global $db;
	global $err;
	$retval = save_attendance($attendance, $notify);
	// if the save found a pre-existing check-in, it returns a 2
	// in that case, we want to DELETE the check-in.
	if (is_array($retval))
	{
		$attendance_id = $retval['id'];
		$sql = sprintf("DELETE FROM attendance WHERE child_id='%s' AND date='%s' AND room_id='%s'",
			$db->escapeString($attendance['child_id']),
			$db->escapeString($attendance['date']),
			$db->escapeString($attendance['room_id']));
		$db->query($sql);

		$sql = sprintf("DELETE FROM attendance_notification_queue WHERE child_id='%s' AND attendance_id='%s'",
			$db->escapeString($attendance['child_id']),
			$db->escapeString($attendance_id));
		$result = $db->query($sql);
		return 2;
	}
	return $retval;
}

function save_attendance($attendance, $notify=false)
{
	global $db;
	global $err;
	$child_id = $attendance['child_id'];
	$date = $attendance['date'] or today();
	$note = $attendance['note'] or '';
	$room_id = $attendance['room_id'];
	// check to see if attendance is already recorded before saving a new one
	$sql = sprintf("SELECT * FROM attendance WHERE child_id='%s' AND date='%s'", $db->escapeString($child_id), $date);
	$result = $db->query($sql);
	//$row = $result->fetchArray(SQLITE3_ASSOC);
	$row = $result->fetch_assoc();
	if ($row)
	{
		$err = 'ERROR: attempted to check-in child # ' . $child_id . ' twice for ' . strftime("%m/%d/%Y", $date) . '.';
		return $row;
	}
	else
	{
		$sql = sprintf("INSERT INTO attendance (child_id, date, room_id, note) VALUES ('%s','%s', '%s', '%s')", $child_id, $date, $room_id, $note);
		$result = $db->query($sql);
		$attendance_id = $db->lastInsertRowid();
		set_child_status($child_id, 'active');
		update_child_last_room($child_id,$room_id);
		if ($notify)
		{
			notify_parent($child_id, '[first_name] is now checked in at "[room]." Please keep your cell phone set to vibrate in case we need to contact you.', $attendance_id );
		}
		return 1;
	}
}

function simple_sms($cell, $msg)
{
	$cell = escapeshellarg($cell);
	$msg = escapeshellarg($msg);
	$cmd = SMS_COMMAND . " $cell $msg > /dev/null 2>&1 &";
	exec($cmd);
}

function notify_parent($child_id, $msg, $attendance_id='', $immediately=false, $force=false)
{
	global $db;

	// if the child is not in attendance, we abort the notification;
	if (! $attendance_id and ! $force ) return "no attendance data set";

	// get the child object
	$child_data = get_children($child_id);
	$cell = $child_data['cell_phone'];

	// if there is no cell phone, abort the notification
	if (! $cell ) return "no cell phone";

	// prepare message for sending
	// message keys like [first_name] or [last_name] are automatically replaced by their data values from the child object.
	foreach ($child_data as $key => $value)
	{
		$msg = str_replace("[$key]", $value, $msg);
	}
	$msg = "INNOVATION KIDS: $msg";
	$cell = escapeshellarg($cell);
	$msg = escapeshellarg($msg);
	if ($immediately)
	{
		$cmd = SMS_COMMAND . " $cell $msg > /dev/null 2>&1 &";
		exec($cmd);
		return "sent immediately";
	}
	else
	{
		// if we are not within 30 minutes of the relevant "attendance time" abort the notification
		// this way, administrative check-ins after the fact don't get notifications
		$sql = sprintf("SELECT * FROM attendance WHERE id='%s'", $db->escapeString($attendance_id));
		$result = $db->query($sql);
		//if ($row = $result->fetchArray(SQLITE3_ASSOC))
		if($row = $result->fetch_assoc())
		{
			$attendance_time = $row['date'];
			$time_diff = abs($attendance_time - time());
			if ($time_diff > 30 * 60) return "automatic notification ignored";
		}

		$sql = sprintf("INSERT INTO attendance_notification_queue (child_id, msg, time, attendance_id) VALUES ('%s', '%s', '%s', '%s')",
			$db->escapeString($child_id),
			$db->escapeString($msg),
			$db->escapeString(time()),
			$db->escapeString($attendance_id) );
		$result = $db->query($sql);
		return "queued for later delivery";
	}
}

function process_notification_queue()
{
	// we don't want to send regular attendance check-in notifications immediately because the quick check-in process
	// is prone to accidental taps. To solve the problem, we only send out notifications after a minute has passed.
	global $db;
	$sql = "SELECT * FROM attendance_notification_queue";
	$result = $db->query($sql);
	//while ($notification = $result->fetchArray(SQLITE3_ASSOC))
	while ($notification = $result->fetch_assoc())
	{
		if (time() > $notification['time'] + 45)
		{
			$child_id = $notification['child_id'];
			$attendance_id = $notification['attendance_id'];
			$msg = $notification['msg'];
			notify_parent($child_id, $msg, $attendance_id, TRUE);

			// delete previous notifications in the queue for this attendance record and this child.
			$sql = sprintf("DELETE FROM attendance_notification_queue WHERE id='%s'",
				$db->escapeString($notification['id']) );
			$result = $db->query($sql);
		}
	}
}


function update_child_last_room($child_id, $room_id)
{
	global $db;
	$sql = sprintf("UPDATE children SET last_room='%s' WHERE id='%s'", $db->escapeString($room_id), $db->escapeString($child_id));
	my_query($sql);
}


function get_attendance_dates()
{
	global $db;
	$sql = "SELECT DISTINCT(date) FROM attendance ORDER BY date DESC";
	$result = $db->query($sql);
	$retval = Array();
	//while ($row = $result->fetchArray(SQLITE3_ASSOC))
	while ($row = $result->fetch_assoc())
		$retval[] = $row['date'];
	return $retval;
}

function refresh_child_status()
{
	global $db;
	global $sunday_timestamp;
	$children=get_children('all','active');
	foreach ($children as $child)
	{
		// get last attendance
		$sql = sprintf("SELECT * FROM attendance WHERE attendance.child_id='%s' ORDER BY date DESC LIMIT 1", $db->escapeString($child['id']));
		$attendance = my_query($sql);
		//debug($child['first_name'] . ' ' . $child['last_name']);
		//debug($sunday_timestamp - $attendance[0]['date']);
		if (! $attendance)
		{
			$child['status'] = 'inactive';
			//debug('NEVER ATTENDED - MARKING INACTIVE');
			//debug($child);
		}
		elseif ($sunday_timestamp - $attendance[0]['date'] > 60*60*24*7*8)
		{
			//debug('OVER EIGHT WEEKS SINCE LAST ATTENDANCE -- MARKING INACTIVE');
			$child['status'] = 'inactive';
			//debug($child);
			//debug($attendance[0]);
		}
		else
		{
			//debug('ATTENDED WITHIN 8 WEEKS -- MARKING ACTIVE');
			$child['status'] = 'active';
			//debug($child['first_name'] . ' ' . $child['last_name']);
		}
		//debug($child);
		save_child($child);
	}
}

// FORMATTING FUNCTIONS
function simple_table($columns, $array, $class)
{
	$odd_or_even = "even";
	if (count($array) == 0)
	{
		print "<table class=\"$class\" cellpadding=0 cellspacing=0><tr><td>no data</td></tr></table>";
		return;
	}

?>

	<table class="<?php print $class; ?>" cellpadding=0 cellspacing=0>
		<tr>

			<?php foreach ($columns as $col) : ?>
			<th><?php print $col; ?></th>
			<?php endforeach; ?>

		</tr>

		<?php foreach ($array as $row) : ?>
		<?php $odd_or_even = ($odd_or_even == "odd") ? "even" : "odd"; ?>

		<tr class="<?php print $odd_or_even; ?>">
			<?php foreach ($columns as $col) : ?>
			<td>

				<?php
				if (is_array($row[$col])) print implode(', ', $row[$col]);
				else print $row[$col];
				?>

			</td>
			<?php endforeach; ?>
		</tr>

		<?php endforeach; ?>

	</table>


<?php
}

function make_table($columns, $data, $table_class="", $link="", $headline)
{
	$keys = Array();
	$col_names = Array();
	foreach ($columns as $col)
	{
		$keys[] = $col[0];
		$col_names[] = $col[1];
	}
	?>

	<?php print $headline; ?>

	<table class="<?php print $table_class; ?>">
		<tr>

			<?php foreach ($col_names as $col_name) : ?>

			<th><?php print $col_name; ?></th>

			<?php if ($link AND is_array($link)) print "<th>$link[name]</th>"; ?>
			<?php endforeach; ?>

		</tr>

		<?php foreach($data as $row) : ?>

		<tr>
			<?php foreach($keys as $key) : ?>
			<td><?php print $row[$key]; ?></td>
			<?php endforeach; ?>
		</tr>

		<?php endforeach; ?>

	</table>

	<?php
}



// INITIALIZATION CODE
$db = new my_db_mysql();
if ($_GET['install'] and $allow_install)
{
	do_install();
}
if ($is_testing)
{
	do_install(TRUE);
	/*CREATE SAMPLE DATABASE ENTRIES
	 - ten households
		(one new household, second week household, third week household all with this sunday as attendance and random other sundays)
		(one household with five attendances for five consecutive weeks ending this week)
		(one household with five attendances for five consecutive weeks ending last week)
		(one household with five attendances for five consecutive weeks ending two weeks ago)
		(one household with five attendances for five consecutive weeks ending three weeks ago)
		(one household with five attendances for five consecutive weeks ending four weeks ago)
		(one household with five attendances for five consecutive weeks ending five weeks ago)
		(one household with five attendances for five consecutive weeks ending six weeks ago)

	 - each household with three kids of random birthdays
		last__week_kid with birthday of sunday - random days between 1 & 7
		this_week_kid with birthday of sunday + random days between 1 & 7
		sunday_kid with birthday on sunday

	foreach household
	1. create the household
	2. create the kids for the households
	3. create the attendance records

	*/

	$testing_household_names = array(
		'new household',
		'second visit',
		'third visit',
		'five consecutive',
		'missed last week',
		'missed two weeks',
		'missed three weeks',
		'missed four weeks',
		'missed five weeks',
		'missed six weeks'
	);

	$children_names = Array(
		Array('birthday_before', 'sunday'),
		Array('birthday_on', 'sunday'),
		Array('birthday_during_this', 'week'),
		Array('birthday_after_this','week')
	);

	$default_household = Array(
		'cell_phone'=>'765-123-4567',
		'address'=>'1234 Anystreet',
		'city'=> 'Lafayette',
		'state'=> 'IN',
		'zip'=> '47909',
		'email'=> 'nospam@neveremail.com',
		'home_phone'=> '765-000-0000'
	);

	$day_of_week = idate("w");
	$today = strtotime(strftime("%m/%d/%Y", time()));
	$sunday = $today - ($day_of_week * 60 * 60 * 24);
	$week = (60*60*24*7);

	foreach ($testing_household_names as $index=>$name)
	{
		$household = $default_household;
		$household['household_name'] = $name;
		$household_id = save_household($household);
		$household['id'] = $household_id;

		foreach ($children_names as $index=>$name)
		{
			$child = Array();
			$child['first_name'] = $name[0];
			$child['last_name'] = $name[1];
			switch ($index)
			{
				case 0:
					$day_diff = (-1) * rand(1,180) - (365 * rand(1,8));
					break;
				case 1:
					$day_diff = 0 - (365 * rand(1,8));
					break;
				case 2:
					$day_diff = rand(1,7) - (365 * rand(1,8));
					break;
				case 3:
					$day_diff = rand(8,180) - (365 * rand(1,8));
					break;
			}
			$child['birthday'] = intval($sunday + (60*60*24*$day_diff));
			$child['birthday'] = strftime("%m/%d/%Y", $child['birthday']);
			$child['allergies'] = 'none';
			$child_id = save_child($child, $household);
			$child['id'] = $child_id;
		}
	}

	// now enter attendance records
	$households = get_households();
	foreach ($households as $household)
	{
		foreach ($household['children'] as $child)
		{
			$child_id = $child['id'];
			$child['room_id'] = get_child_room($child);
			$room_id = $child['room_id'];
			$attendance = Array(
				'child_id' => $child_id,
				'room_id' => $room_id,
				'note' => ''
			);
			$missed_weeks = 0;

			switch ($household['household_name'])
			{
				case 'third visit':
					$visit_date = $sunday - ($week * rand(5,8));
					$attendance['date'] = $visit_date;
					save_attendance($attendance);
				case 'second visit':
					$visit_date = $sunday - ($week * rand(1,4));
					$attendance['date'] = $visit_date;
					save_attendance($attendance);
				case 'new household':
					$visit_date = $sunday;
					$attendance['date'] = $visit_date;
					save_attendance($attendance);
					break;

				case 'missed six weeks':
					$missed_weeks += 1;
				case 'missed five weeks':
					$missed_weeks += 1;
				case 'missed four weeks':
					$missed_weeks += 1;
				case 'missed three weeks':
					$missed_weeks += 1;
				case 'missed two weeks':
					$missed_weeks += 1;
				case 'missed last week':
					$missed_weeks += 1;
				case 'five consecutive':
					for ($i = $missed_weeks; $i < 10; $i++)
					{
						$attendance['date'] = $sunday - ($week * $i);
						save_attendance($attendance);
					}
			}
		}
	}
}


/* AUTHENTICATION */
function logout()
{
	global $cookiename;
	unset( $_SESSION['logged_in'] );
	setcookie($cookiename, "", time() - 3600);
}

function authenticate()
{
	global $cookiename;
	// process posted data
	if (isset($_POST['key']) and $_POST['key'] == 'innovation')
	{
		$_SESSION['logged_in'] = True;
		setcookie($cookiename, "True");
		return;
	}
	elseif (isset($_POST['key']) and $_POST['key'] == 'register')
	{
		$_SESSION['logged_in'] = 'public';
		setcookie($cookiename, "True");
		return;
	}

	// look for session data and cookies already set
	if ( isset($_SESSION['logged_in']) or isset($_COOKIE[$cookiename]) or isset($_GET['login_override'])) return;

	// show login form
	logout();

	?>

	<html>
		<head>
			<title>Innovation Kids Login</title>
			<style type="text/css">
				body {margin: 200px auto; width: 500px;background: #222; color: white;font-size: 14pt;text-align:left;}
				input {font-family: "Trebuchet MS", "Tahoma", sans-serif; font-size: 24pt;border:5px solid cyan; border-radius: 10px;padding: 15px;}


			</style>
		</head>
		<body>
			<form method="POST">
				<label for="key">Enter Password:</label><br /><input type="password" name="key" id="key" /><input type="submit" value="Go" />
			</form>
		</body>
	</html>


	<?php

	exit();

}

process_notification_queue();
?>
