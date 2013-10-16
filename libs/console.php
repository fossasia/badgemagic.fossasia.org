<?php
/**
	* Logs messages/variables/data to browser console from within php
	*
	* @param $name: message to be shown for optional data/vars
	* @param $data: variable (scalar/mixed) arrays/objects, etc to be logged
	* @param $jsEval: whether to apply JS eval() to arrays/objects
	*
	* @return none
	* @author Sarfraz
*/
function logConsole($name, $data = NULL, $jsEval = FALSE){

          if (! $name) return false;
 
          $isevaled = false;
          $type = ($data || gettype($data)) ? 'Type: ' . gettype($data) : '';
 
          if ($jsEval && (is_array($data) || is_object($data)))
          {
               $data = 'eval(' . preg_replace('#[\s\r\n\t\0\x0B]+#', '', json_encode($data)) . ')';
               $isevaled = true;
          }
          else
          {
               $data = json_encode($data);
          }
 
          # sanitalize
          $data = $data ? $data : '';
          $search_array = array("#'#", '#""#', "#''#", "#\n#", "#\r\n#");
          $replace_array = array('"', '', '', '\\n', '\\n');
          $data = preg_replace($search_array,  $replace_array, $data);
          $data = ltrim(rtrim($data, '"'), '"');
          $data = $isevaled ? $data : ($data[0] === "'") ? $data : "'" . $data . "'";
 
$js = <<<JSCODE
\n<script>
     // fallback - to deal with IE (or browsers that don't have console)
     if (! window.console) console = {};
     console.log = console.log || function(name, data){};
     // end of fallback
 
     console.log('$name');
     console.log('------------------------------------------');
     console.log('$type');
     console.log($data);
     console.log('\\n');
</script>
JSCODE;

     echo $js;
} # end logConsole

/*
 * EXAMPLE USES
 * $name = 'sarfraz';
 
 * $fruits = array("banana", "apple", "strawberry", "pineaple");
 
 * $user = new stdClass;
 * $user->name = "Sarfraz";
 * $user->desig = "Sr. Software Engineer";
 * $user->lang = "PHP";
 
 * logConsole('$name var', $name, true);
 * logConsole('An array of fruits', $fruits, true);
 * logConsole('$user object', $user, true);
 
 */


/*
WORPRESS EXTEND
Change Search And Replace for:
$search_array = array("/=\'(\w*)\'/", "#'#", '#""#', "#''#", "#\n#", "#\r\n#", "/:,/", "/(\d{1,}),(\d{1,})/");
$replace_array = array('=\"$1\"','"', '', '', '\\n', '\\n', ":\"\",", '$1-$2');

post associated:
http://sarfraznawaz.wordpress.com/2012/01/05/outputting-php-to-browser-console/
*/
?>