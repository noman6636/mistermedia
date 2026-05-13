<?php if ( ! defined('BASEPATH')) exit ('No direct script  allow'); 

class Common_model extends  CI_Model {
    
    function get_query($query)
	{
		return $this->db->query( $query );
	}
	
	function select_all($select,$table)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		return $this->db->get();
	}
	
	function select_where($select,$table,$where)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->where( $where );
		return $this->db->get();
	}
	
	function select_groupby($select,$table,$where,$groupby)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->where( $where );
		$this->db->group_by( $groupby ); 
		return $this->db->get();
	}
	
	
	function select_groupby_orderby($select,$table,$where,$groupby,$orderBy_columName,$ASC_DESC)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->where( $where );
		$this->db->group_by( $groupby ); 
		$this->db->order_by( $orderBy_columName , $ASC_DESC );
		return $this->db->get();
	}

	
	

	function select_distinct($select,$table,$where)
	{	
		$this->db->distinct($select);
		$this->db->from( $table );
		$this->db->where( $where );
		return $this->db->get();
	}
	
	
	function select_where_in($select,$table,$where_in,$in_fld)
	{	
		$this->db->select($select);
		$this->db->from( $table );
		$this->db->where_in($in_fld, $where_in);
		$this->db->group_by($select);
		return $this->db->get();
	}
	
	
	function select_single_field($select,$table,$where)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->where( $where );
		$qry = $this->db->get();
		$rr	=	$qry->row_array();
		return	$rr[$select];
	}
	
	function select_limit_order($select,$table,$page,$recordperpage,$orderBy_columName,$ASC_DESC)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->limit( $recordperpage , $page );
		$this->db->order_by( $orderBy_columName , $ASC_DESC );
		$result=$this->db->get();
		return $result;	
		
	}
	
	function select_where_ASC_DESC( $select,$table,$where,$orderBy_columName,$ASC_DESC )
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->where( $where );
		$this->db->order_by( $orderBy_columName , $ASC_DESC );
		$result=$this->db->get();
		return $result;	
		
	}
	
	function select_where_wherein_ASC_DESC( $select,$table,$where,$in_fld,$where_in,$groupby,$orderBy_columName,$ASC_DESC )
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->where( $where );
		$this->db->where_in($in_fld, $where_in);
		$this->db->group_by($groupby);
		$this->db->order_by( $orderBy_columName , $ASC_DESC );
		$result=$this->db->get();
		return $result;	
		
	}

	
	function select_where_order($select,$table,$orderBy_columName,$ASC_DESC)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->order_by( $orderBy_columName , $ASC_DESC );
		$result=$this->db->get();
		return $result;	
		
	}
	
	function select_where_limit_order($select,$table,$where,$page,$recordperpage,$orderBy_columName,$ASC_DESC)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->where( $where );
		$this->db->limit( $recordperpage , $page );
		$this->db->order_by( $orderBy_columName , $ASC_DESC );
		$result=$this->db->get();
		return $result;	
		
	}
	
	function select_where_table_rows($select,$table,$where)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->where( $where );
		$query=$this->db->get();
		return $query->num_rows();
	}
	
	function select_where_return_row($select,$table,$where)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->where( $where );
		$query=$this->db->get();
		return $query->row();
	}
	
	function select_limit($select,$table,$page,$recordperpage)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->limit( $recordperpage , $page );
		$result=$this->db->get();
		return $result;	
		
	}

	
	function select_table_rows($select,$table)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$query=$this->db->get();
		return $query->num_rows();
	}
	
	
	
	function update_array($where,$table,$data)
	{
		$this->db->where( $where );
		$this->db->update( $table , $data);	
	}
	
	function insert_array($table,$data)
	{
		$this->db->insert( $table,$data );
		return $this->db->insert_id();	
	}
	
	function delete_where($where,$tbl_name)
	{
		$this->db->where($where);
		$this->db->delete($tbl_name);
	}
	
	function join_two_tab( $select , $from , $jointab , $condition, $orderBy_columName , $ASC_DESC ){
	
			$this->db->select( $select );
			$this->db->from( $from );
			$this->db->join( $jointab, $condition );
			$this->db->order_by( $orderBy_columName , $ASC_DESC );			
			return $this->db->get();
		
	}
	
	function join_two_tab_where( $select, $from, $jointable, $condition, $where, $recordperpage, $page, $orderBy_columName, $ASC_DESC ){
		$this->db->select($select);
		$this->db->from( $from );
		$this->db->join( $jointable , $condition );
		$this->db->where( $where );
		$this->db->limit( $recordperpage , $page );
		$this->db->order_by( $orderBy_columName , $ASC_DESC );	
		return $this->db->get();

	}
	
	
	function join_two_tab_where_numrow( $select, $from, $jointable, $condition, $where ){
		$this->db->select($select);
		$this->db->from( $from );
		$this->db->join( $jointable , $condition );
		$this->db->where( $where );
		$query=$this->db->get();
		return $query->num_rows();

	}
	
	
	function select_or_like( $select,$table,$where,$orcondition,$recordperpage,$page,$orderBy_columName,$ASC_DESC ){
		$this->db->select( $select );
		$this->db->from( $table );
		//$this->db->like( $like );
		$this->db->or_like($orcondition); 
		$this->db->where( $where );
		$this->db->limit( $recordperpage , $page );
		$this->db->order_by( $orderBy_columName , $ASC_DESC );			
		return $this->db->get();
	
	}
	
	function like_search( $select,$table,$where,$like,$orderBy_columName,$ASC_DESC ){
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->or_like($like); 
		$this->db->order_by( $orderBy_columName , $ASC_DESC );			
		$this->db->where( $where );
		return $this->db->get();
	
	}
	
	
	function select_or_like_rows( $select,$table,$where,$orcondition ){
		$this->db->select( $select );
		$this->db->from( $table );
		//$this->db->like( $like );
		$this->db->or_like($orcondition); 		
		$this->db->where( $where );
		$query=$this->db->get();
		return $query->num_rows();
	
	}
	
	
	function join_tab_where( $select , $from , $jointab , $condition, $where, $orderBy_columName , $ASC_DESC ){
	
			$this->db->select( $select );
			$this->db->from( $from );
			$this->db->join( $jointab, $condition );
			$this->db->where( $where );
			$this->db->order_by( $orderBy_columName , $ASC_DESC );			
			return $this->db->get();
	}
	
	
	function select_where_like($select,$table,$where_con,$where,$limit)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->where( $where_con );
		$this->db->like($where); 
		$this->db->limit($limit);
		return $this->db->get();
	}
	
	function select_where_like_order_by( $select,$table,$where,$like,$orderBy_columName,$ASC_DESC ){
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->where( $where );
		$this->db->like($like); 
		$this->db->order_by( $orderBy_columName , $ASC_DESC );			
		return $this->db->get();
	
	}

	function join_three_tab_where( $select, $from, $jointable1, $condition1, $jointable2, $condition2,  $where, $recordperpage, $page, $orderBy_columName, $ASC_DESC ){
		$this->db->select($select);
		$this->db->from( $from );
		$this->db->join( $jointable1 , $condition1 );
		$this->db->join( $jointable2 , $condition2 );
		$this->db->where( $where );
		$this->db->limit( $recordperpage , $page );
		$this->db->order_by( $orderBy_columName , $ASC_DESC );	
		return $this->db->get();
	}
	
	function join_three_tab_where_rows( $select, $from, $jointable1, $condition1, $jointable2, $condition2,  $where ){
		$this->db->select($select);
		$this->db->from( $from );
		$this->db->join( $jointable1 , $condition1 );
		$this->db->join( $jointable2 , $condition2 );
		$this->db->where( $where );
		$query	=	$this->db->get();
		return 		$query->num_rows();
	}
	
	function select_limit_by($select,$table,$where,$page,$recordperpage,$orderBy_columName,$ASC_DESC)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->where( $where );
		$this->db->limit( $recordperpage , $page );
		$this->db->order_by( $orderBy_columName , $ASC_DESC );
		$result=$this->db->get();
		return $result;	
	}
	
	function join_two_tab_where_numrows( $select, $from, $jointable, $condition, $where ){
		$this->db->select($select);
		$this->db->from( $from );
		$this->db->join( $jointable , $condition );
		$this->db->where( $where );
		return $this->db->get();
	}
	
	function select_limit_where($select,$table,$where,$page,$recordperpage)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->where( $where );
		$this->db->limit( $recordperpage , $page );
		$result=$this->db->get();
		return $result;	
	}
	
	function select_table_rows_where($select,$table,$where)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->where( $where );
		$query=$this->db->get();
		return $query->num_rows();
	}
	
	function join_two_tab_where_limit( $select, $from, $jointable, $condition,$where,$page,$recordperpage ){
		$this->db->select($select);
		$this->db->from( $from );
		$this->db->join( $jointable , $condition );
		$this->db->where( $where );
		$this->db->limit( $recordperpage , $page );
		$query=$this->db->get();
		return $query;
	}
	
	function join_two_tab_where_numrw( $select, $from, $jointable, $condition,$where){
		$this->db->select($select);
		$this->db->from( $from );
		$this->db->join( $jointable , $condition );
		$this->db->where( $where );
		 $query=$this->db->get();
		 return $query->num_rows();
	}
	
	function join_two_tab_where_simple( $select, $from, $jointable, $condition, $where ){
		$this->db->select($select);
		$this->db->from( $from );
		$this->db->join( $jointable , $condition );
		$this->db->where( $where );
		$query=$this->db->get();
		return $query;
	}
	
	function join_two_tab_where_groupby( $select, $from, $jointable, $condition, $where ,$group_by ){
		$this->db->select($select);
		$this->db->from( $from );
		$this->db->join( $jointable , $condition );
		$this->db->where( $where );
		$this->db->group_by( $group_by );
		$query=$this->db->get();
		return $query;
	}
	
	function select_limit_order_where($select,$table,$where,$page,$recordperpage,$orderBy_columName,$ASC_DESC)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->where( $where );
		$this->db->limit( $recordperpage , $page );
		$this->db->order_by( $orderBy_columName , $ASC_DESC );
		$result=$this->db->get();
		return $result;	
		
	}
		
	function listing_order($table,$col,$ac)
	{
		$this->db->select('*');
		$this->db->from($table);
		$this->db->order_by($col,$ac); 
		return $result = $this->db->get();
	}
	 	
	function select_where_numrow_return_single($select,$table,$where)
	{
		$this->db->select( $select );
		$this->db->from( $table );
		$this->db->where( $where );
		$query	=	$this->db->get();
		if($query->num_rows() > 0)
		{
			$row	=	$query->row();
			return	$row->$select;
		} else {
			return  0;
		}
	}
	
	function htmlToPlainText($str){
        $str = str_replace('&nbsp;', ' ', $str);
        $str = html_entity_decode($str, ENT_QUOTES | ENT_COMPAT , 'UTF-8');
        $str = html_entity_decode($str, ENT_HTML5, 'UTF-8');
        $str = html_entity_decode($str);
        $str = htmlspecialchars_decode($str);
        $str = strip_tags($str);
    
        return $str;
    }
    
    function trim_paragraph($string){
        
        return strlen($string) > 60 ? substr($string,0,60)."..." : $string;;
    }
	
	############################# Admin Login #########################
	
	function admin_login($table,$username,$password,$get_field)
	{
		$sql		=	'select * from '.$table.' where '.$get_field.' = "'.$username.'" and password = "'.$password.'" and deleted = 0';	
		return $this->db->query($sql);
	}
	
	function check_ip($login_ip,$id)
	{
		$sql	=	'SELECT ip FROM '.APP_ADMIN_LOGIN_RESTRICT_IPS.' WHERE admin_id = "'.$id.'" and ip = "'.$login_ip.'" '; 
		//print $sql; exit;
		return $result =  $this->db->query($sql);
	}
	
	
}