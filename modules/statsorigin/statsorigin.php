<?php

/**
  * Statistics
  * @category stats
  *
  * @author Damien Metzger / Epitech
  * @copyright Epitech / PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.1
  */
  
class StatsOrigin extends ModuleGraph
{
	private $_html;
	
    function __construct()
    {
        $this->name = 'statsorigin';
        $this->tab = 'Stats';
        $this->version = 1.0;
		$this->page = basename(__FILE__, '.php');
        parent::__construct();
		
        $this->displayName = $this->l('Visitors origin');
        $this->description = $this->l('Display the websites from where your visitors come from');
    }

	function install()
	{
		return (parent::install() AND $this->registerHook('AdminStatsModules'));
	}

	private function getOrigins()
	{
		$result = mysql_query('
		SELECT http_referer
		FROM '._DB_PREFIX_.'connections
		WHERE date_add LIKE \''.pSQL(ModuleGraph::getDateLike()).'\'');
		$websites = array('Direct link' => 0);
		while ($row = mysql_fetch_assoc($result))
		{
			if (!isset($row['http_referer']) OR empty($row['http_referer']))
				++$websites['Direct link'];
			else
			{
				$website = preg_replace('/^www./', '', parse_url($row['http_referer'], PHP_URL_HOST));
				if (!isset($websites[$website]))
					$websites[$website] = 1;
				else
					++$websites[$website];
			}
		}
		mysql_free_result($result);
		arsort($websites);
		return $websites;
	}

	function hookAdminStatsModules()
	{
		$websites = $this->getOrigins();
		
		$this->_html = '<fieldset class="width3"><legend><img src="../modules/'.$this->name.'/logo.gif" /> Origin</legend>';
		if (sizeof($websites))
		{
			$this->_html .= '
			<center>
			<p><img src="../img/admin/down.gif" />Here are the percentages of the 10 most websites............. </p>
			'.ModuleGraph::engine(array('type' => 'pie')).'
			<br /><br /><br /><table class="table" border="0" cellspacing="0" cellspacing="0">
				<tr>
					<th style="width:400px;">'.$this->l('Origin').'</th>
					<th style="width:50px; text-align: right">'.$this->l('Total').'</th>
				</tr>';
			foreach ($websites as $website => $total)
				$this->_html .= '<tr><td>'.(!strstr($website, ' ') ? '<a href="http://'.$website.'">' : '').$website.(!strstr($website, ' ') ? '</a>' : '').'</td><td style="text-align: right">'.$total.'</td></tr>';
			$this->_html .= '</table></center>';
		}
		else
			$this->_html .= '<p><strong>'.$this->l('Direct links only').'</strong></p>';
		$this->_html .= '</fieldset>';
		return $this->_html;
	}
		
	protected function getData($layers)
	{
		$this->_titles['main'] = $this->l('10 first websites');
		$websites = $this->getOrigins();
		$total = 0;
		$total2 = 0;
		$i = 0;
		foreach ($websites as $website => $totalRow)
		{
			$total += $totalRow;
			if ($i++ < 9)
			{
				$this->_legend[] = $website;
				$this->_values[] = $totalRow;
				$total2 += $totalRow;
			}
		}
		if ($total != $total2)
		{
			$this->_legend[] = $this->l('Others');
			$this->_values[] = $total - $total2;
		}
	}
}

?>
