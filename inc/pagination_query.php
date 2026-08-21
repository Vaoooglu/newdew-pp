<?php
/*
* "Пагинация" для WordPress WP_Query
* автор: vaoooglu.dn@gmail.com
* версия: 2019.03.03
* лицензия: MIT
* if ( function_exists( 'the_pagination_query' ) ) the_pagination_query($query_works->max_num_pages);
*/

function the_pagination_query($pages = '', $range = 2)
{
    $showitems = $range +1;

    global $paged;
    if(empty($paged)) $paged = 1;
    if($pages == '')
    {
    global $wp_query;
    $pages = $wp_query->max_num_pages;
    
    if(!$pages)
    {
        $pages = 1;
    }
    }
    if(1 != $pages)
    {
//        if($paged > 2 && $paged > $range+1 && $showitems < $pages) echo "<a class=\"pagination__item prev-page\" href='".get_pagenum_link(1)."'></a>";
        if($paged > 1 && $showitems < $pages) echo "<a class=\"pagination__item prev-page\" href='".get_pagenum_link($paged - 1)."'></a>";
        for ($i=1; $i <= $pages; $i++)
        {
            if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ))
            {
                echo ($paged == $i)? "<a class=\"pagination__item page current\">".$i."</a>":"<a href='".get_pagenum_link($i)."' class=\"pagination__item page\">".$i."</a>";
            }
        }
        if ($paged < $pages && $showitems < $pages) echo "<a href=\"".get_pagenum_link($paged + 1)."\" class=\"pagination__item next-page\"></a>";
//        if ($paged < $pages-1 && $paged+$range-1 < $pages && $showitems < $pages) echo "<a href='".get_pagenum_link($pages)."'>Конец &raquo</a>";

    }
}

