<?php 
/**

 * @name PHPTree 

 * @author crazymus < QQ:291445576 >

 * @des PHP生成树形结构,无限多级分类

 * @version 1.2.0

 * @Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 * @updated 2015-08-26

 */
 namespace Common\Common;
class Mytree{	

	private $config = array(
		/* 主键 */
		'primary_key' 	=> 'id',
		/* 父键 */
		'parent_key'  	=> 'parent_id',
		/* 展开属性 */
		'expanded_key'  => 'expanded',
		/* 叶子节点属性 */
		'leaf_key'      => 'leaf',
		/* 孩子节点属性 */
		'children_key'  => 'children',
		/* 是否展开子节点 */
		'expanded'    	=> false
	);
	
	/* 结果集 */
	private $result = array();
	
	/* 层次暂存 */
	private  $level = array();
	
	
	public function __construct($options=array()){
		$this->config = array_merge($this->config,$options);
	}
	
	/**

	 * @name 生成树形结构

	 * @param array 二维数组

	 * @return mixed 多维数组

	 */
	public  function makeTree($data){
		$dataset = $this->buildData($data);
		$r = $this->makeTreeCore(0,$dataset,'normal');
		return $r;
	}
	
	/* 生成线性结构, 便于HTML输出, 参数同上 */
	public function makeTreeForHtml($data){
	
		$dataset = $this->buildData($data);
		$r = $this->makeTreeCore(0,$dataset,'linear');
		return $r;	
	}
	
	/* 格式化数据, 私有方法 */
	private function buildData($data){
		extract($this->config);

		$r = array();
		foreach($data as $item){
			$id = $item[$primary_key];
			$parent_id = $item[$parent_key];
			$r[$parent_id][$id] = $item;
		}
		
		return $r;
	}
	
	/* 生成树核心, 私有方法  */
	private function makeTreeCore($index,$data,$type='linear')
	{
		extract($this->config);
		foreach($data[$index] as $id=>$item)
		{
			if($type=='normal'){
				if(isset($data[$id]))
				{
					$item[$expanded_key]= $this->config['expanded'];
					$item[$children_key]= $this->makeTreeCore($id,$data,$type);
				}
				else
				{
					$item[$leaf_key]= true;  
				}
				$r[] = $item;
			}else if($type=='linear'){
				$parent_id = $item[$parent_key];
				$this->level[$id] = $index==0?0:$this->level[$parent_id]+1;
				$item['level'] = $this->level[$id];
				$this->result[] = $item;
				if(isset($data[$id])){
					$this->makeTreeCore($id,$data,$type);
				}
				
				$r = $this->result;
			}
		}
		return $r;
	}
}
