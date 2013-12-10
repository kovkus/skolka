<?php

class Paginator{
	var $items_per_page;
	var $items_total;
	var $current_page;
	var $num_pages;
	var $mid_range;
	var $low;
	var $high;
	var $limit;
	var $return;
	var $default_ipp = 25;
	var $querystring;
	var $show_path;

	function Paginator()
	{
		$this->current_page = 1;
		$this->mid_range = 7;
		$this->items_per_page = $this->default_ipp;
	}

	function paginate()
	{
    global $default_template, $dir, $lang, $mn_tmpl, $mn_count;
    $mn_pages = ''; $mn_previous = ''; $mn_next = '';
    
    
		if (!is_numeric($this->items_per_page) || $this->items_per_page <= 0) $this->items_per_page = $this->default_ipp;
		$this->num_pages = ceil($this->items_total/$this->items_per_page);

		$this->current_page = (int)$_GET['mn_p']; // must be numeric > 0
		if($this->current_page < 1 || !is_numeric($this->current_page)) $this->current_page = 1;
		if($this->current_page > $this->num_pages) $this->current_page = $this->num_pages;
		$prev_page = $this->current_page-1;
		$next_page = $this->current_page+1;

		
		$mn_previous = ($this->current_page != 1 && $this->items_total >= $mn_count) ? '<a href="' . $this->show_path . 'mn_p=' . $prev_page . '">$1</a>' : '<span class="inactive">$1</span>';
		$mn_next = (($this->current_page != $this->num_pages && $this->items_total >= $mn_count) && ($_GET['mn_p'] != 'All')) ? '<a href="' . $this->show_path . 'mn_p=' . $next_page . '">$1</a>' : '<span class="inactive">$1</span>';


		if($this->num_pages > 10)
		{

			$this->start_range = $this->current_page - floor($this->mid_range/2);
			$this->end_range = $this->current_page + floor($this->mid_range/2);

			if ($this->start_range <= 0)
			{
				$this->end_range += abs($this->start_range)+1;
				$this->start_range = 1;
			}
			if ($this->end_range > $this->num_pages)
			{
				$this->start_range -= $this->end_range-$this->num_pages;
				$this->end_range = $this->num_pages;
			}
			$this->range = range($this->start_range,$this->end_range);

			for ($i=1; $i<=$this->num_pages; $i++)
			{
				if ($this->range[0] > 2 && $i == $this->range[0]) $mn_pages .= ' &hellip; ';
				// loop through all pages. if first, last, or in range, display
				if ($i==1 Or $i==$this->num_pages Or in_array($i,$this->range))
				{
					$mn_pages .= ($i == $this->current_page) ? '<span class="current">' . $i . '</span> ' : '<a href="' . $this->show_path . 'mn_p=' . $i . '">' . $i . '</a> ';
				}
				if ($this->range[$this->mid_range-1] < $this->num_pages-1 && $i == $this->range[$this->mid_range-1]) $mn_pages .= " &hellip; ";
			}

		}
		else
		{
			for ($i=1; $i<=$this->num_pages; $i++)
			{
				$mn_pages .= ($i == $this->current_page) ? '<span class="current">' . $i . '</span> ' : '<a href="' . $this->show_path . 'mn_p=' . $i . '">' . $i . '</a> ';
			}
		}
		$this->low = ($this->current_page-1) * $this->items_per_page;
		$this->high = ($this->current_page * $this->items_per_page)-1;
		$this->limit = " LIMIT $this->low, $this->items_per_page";


		$tmpl_file = (file_exists(MN_ROOT . $dir['templates'] . $mn_tmpl . '_15' . '.html')) ? file_get_contents(MN_ROOT . $dir['templates'] . $mn_tmpl . '_15' . '.html') : file_get_contents(MN_ROOT . $dir['templates'] . DEFAULT_TMPL . '_15.html');
		if (empty($tmpl_file)) $tmpl_file = $default_template[15];

		$tmpl_preg_search = array(
      '/\[PREVIOUS\](.*?)\[\/PREVIOUS\]/is',
      '/\[NEXT\](.*?)\[\/NEXT\]/is',
    );
    $tmpl_preg_replace = array(
      $mn_previous,
      $mn_next,
    );

    $pagination_result = str_ireplace('{PAGES}', $mn_pages, $tmpl_file);
    $this->return = preg_replace($tmpl_preg_search, $tmpl_preg_replace, $pagination_result);
	}

	function display_pages()
	{
		return $this->return;
	}
}
