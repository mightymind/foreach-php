<?

class Objctrl {

public $objs=array();

	function __construct()
	{
		
		}
	
	public function create($id,$params=array())
	{
		$this->objs[$id]=new stdClass();
		return $this->update_params($id,$params=array())
		}
	
	public function create_child($parent,$id,$params=array())
	{
		if($this->create($id,$params=array())) {
			return $this->set_child($parent,$id);
			} else {
				return false;
				}
		}
	
	public function set_child($parent,$id)
	{
		if(isset($this->objs[$parent]) && isset($this->objs[$id])) {
			$this->objs[$parent]->children[$id]=$id;
			$this->objs[$id]->parents[$parent]=$parent;
			return true;
			} else {
				return false;
				}
		}
	
	public function remove_child($parent,$id)
	{
		if(isset($this->objs[$parent]) && isset($this->objs[$id])) {
			unset($this->objs[$parent]->children[$id]);
			unset($this->objs[$id]->parents[$parent]);
			return true;
			} else {
				return false;
				}
		}
	
	public function obj($id)
	{
		return $this->objs[$id];
		}
	
	public function get_param($id,$param)
	{
		return $this->objs[$id]->$param;
		}
	
	public function update_params($id,$params=array())
	{
		if(count($params)) {
			foreach($params as $key=>$value) {
				$this->objs[$id]->$key=$value;
				}
			}
		return true;
		}
	
	public function set_obj($id,&$obj)
	{
		$this->objs[$id]=&$obj;
		return true;
		}
	
	public function destroy_obj($id)
	{
		if(count($this->objs[$id]->children)) {
			foreach($this->objs[$id]->children as $child) {
				unset($this->objs[$id]->children[$child]);
				unset($this->objs[$child]->parents[$id]);
				}
			}
		if(count($this->objs[$id]->parents)) {
			foreach($this->objs[$id]->parents as $parent) {
				unset($this->objs[$parent]->children[$id]);
				unset($this->objs[$id]->parents[$parent]);
				}
			}
		unset($this->objs[$id]);
		return true;
		}
	
	public function destroy()
	{
		unset($this->objs);
		unset($this);
		}

}

?>