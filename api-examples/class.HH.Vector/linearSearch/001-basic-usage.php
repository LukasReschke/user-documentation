<?hh

namespace Hack\UserDocumentation\API\Examples\Vector\LinearSearch;

$v = Vector {'red', 'green', 'blue', 'yellow'};

// Prints 2
var_dump($v->linearSearch('blue'));

// Prints -1 (since 'purple' is not in the Vector)
var_dump($v->linearSearch('purple'));
