<?php
/**
 * **双向链表
 * @author tju_zyt@tju.edu.cn
 *
 */

 /**
 * 链表元素结点类
 */
class Node{
    public $prev = NULL; // 前驱
	public $next = NULL; // 后继
	public $key = NULL; // 元素键值
	public $data = NULL; // 结点值

	function __Construct($key, $data) {
		$this->key = $key;
		$this->data = $data;
	}
}

class DoubleLinkList{
    private $head;
    private $tail;
    private $current;
    private $size;

    function __Construct(){
        $this->head = $this->tail = NULL;
		$this->size = 0;
    }

    /**
     * 在链表头部加入节点
     * @param $key 要插入元素的key
     * @param $data 要插入链表元素的数据
     */
    public function addFirst($key, $data){
        $newNode = new Node($key, $data);
        $newNode->next = $this->head;
        $this->head->prev->next = $newNode;
        $this->head->prev = $newNode;
        $this->head = $newNode;
        $this->size++;

        if($this->tail === NULL){
            $this->tail = $this->head;
        }
    }

    /**
     * 在链表尾部加入节点
     * @param $key 要插入元素的key
     * @param $data 要插入链表元素的数据
     */
    public function addLast($key, $data){
        if($this->tail === NULL){
            $this->addFirst($key, $data);
        }else{
            $newNode = new Node($key, $data);
            $newNode->prev = $this->tail;
            $this->tail->next->prev = $newNode;
            $this->tail->next = $newNode;
            $this->tail = $newNode;
        }
        $this->size++;
    }

    /**
     * 删除链表头部节点
     */
    public function removeFirst(){
        if($this->size == 0){
            throw new Exception("The list is empty");
        } else {
            $tmp = $this->head;
            $this->head = $tmp->next;
            $this->head->prev = $tmp->prev;
            if($this->head === NULL) $this->tail = NULL;
            unset($tmp);
            $this->size--;
        }
    }

    /**
     * 删除链表尾部节点
     */
    public function removeLast(){
        if($this->size == 0){
            throw new Exception("The list is empty");
        } else {
            $tmp = $this->tail;
            $this->tail = $tmp->prev;
            $this->tail->next = $tmp->next;
            if($this->tail === NULL) $this->head = NULL;
            unset($tmp);
            $this->size--;
        }
    }

    /**
     * 获得头部节点的数据
     * @throws 链表为空
     */
     public function getFirst(){
         if($this->size == 0){
             throw new Exception("The list is empty");
         } else {
             var_dump($this->head->key, $this->head->data);
         }
     }

     /**
      * 获得尾部节点的数据
      * @throws 链表为空
      */
      public function getLast(){
          if($this->size == 0){
              throw new Exception("The list is empty");
          } else {
              var_dump($this->tail->key, $this->tail->data);
          }
      }

      /**
      * 在指定位置插入节点
      * @param $pos 指定元素在列表的位置
      * @param $key 要插入元素的key
      * @param $data 要插入链表元素的数据
      */
      public function add($pos, $key, $data){
          if($pos == 0){
              $this->addFirst($key, $data);
          }else if($pos >= $this->size){
              $this->addLast($key, $data);
          }else{
              $this->current = $this->head;
              for($i = 1; $i < $pos; $i++){
                  $this->current = $this->current->next;
              }
              $tmp = $this->current->next;
              $newNode = new Node($key, $data);
              $newNode->next = $tmp;
              $newNode->prev = $this->current;
              $this->current->next = $newNode;
              $tmp->prev = $newNode;
              $this->size++;
          }
      }


      /**
      * 在指定位置删除节点
      * @param $pos 指定元素在列表的位置
      * @param $key 要插入元素的key
      * @param $data 要插入链表元素的数据
      */
      public function remove($pos){
          if($pos == 0){
              $this->removeFirst();
          }else if($pos >= $this->size){
              $this->removeLast();
          }else{
              $this->current = $this->head;
              for($i = 1; $i < $pos; $i++){
                  $this->current = $this->current->next;
              }
              $tmp = $this->current->next;
              $this->current->next = $tmp->next;
              $tmp->next->prev = $this->current;
              $this->size--;
          }
      }

     /**
      * 获得所有节点的数据
      * @throws 链表为空
      */
      public function getAll(){
          if($this->size == 0){
              throw Exception("The list is empty");
          } else {
              $tmp = $this->head;
              while($tmp !== NULL){
                  var_dump($tmp->key, $tmp->data);
                  $tmp = $tmp->next;
              }
          }
      }

      /**
      * 根据key获得指定节点的数据
      * @param $key 指定的key值
      * @return $data 对应的数据
      */
      public function findByKey($key){
          $tmp = $this->head;
          while ($tmp !== NULL) {
              if ($tmp->key === $key) {
                  return $tmp->data;
              }
			  $tmp = $tmp->next;
		  }
          return null;
      }

      /**
      * 根据pos获得指定节点的数据
      * @param $pos 指定的key值
      * @return $tmp 对应的key和data数据
      */
      public function findByPos($pos){
          if ($pos <= 0 || $pos > $this->size)
			return null;
		if ($pos < ($this->size / 2 + 1)) {
			$tmp = $this->head;
			$count = 0;
			while ( $tmp->next !== null ) {
				$tmp = $tmp->next;
				$count++;
				if ($count === $pos) {
					return $tmp;
				}
			}
		} else {
			$tmp = $this->tail;
			$pos = $this->size - $pos + 1;
			$count = 1;
			while ( $tmp->prev !== null ) {
				if ($count === $pos) {
					return $tmp;
				}
				$tmp = $tmp->pre;
				$count++;
			}
		}
		return null;
      }

      /**
      * 清空链表
      */
      public function clear(){
          while($this->head !== NULL){
              $tmp = $this->head;
              $this->head = $this->head->next;
              unset($tmp);
          }
      }
}
