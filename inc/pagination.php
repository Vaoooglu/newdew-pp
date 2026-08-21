<?php
/*
* "Пагинация" для WordPress
* автор: vaoooglu.dn@gmail.com
* версия: 2019.03.03
* лицензия: MIT
* if ( function_exists( 'the_pagination' ) ) the_pagination();
*/


function the_pagination() {
  global $wp_query;
  $pages = "";
  $max = $wp_query->max_num_pages;
  if (!$current = get_query_var("paged")) $current = 1;
  $a["base"] = str_replace(999999999, "%#%", get_pagenum_link(999999999));
  $a["total"] = $max;
  $a["current"] = $current;
  $total = 0; //1 - выводить текст "Страница N из N", 0 - не выводить
  $a["mid_size"] = 3; //сколько ссылок показывать слева и справа от текущей
  $a["end_size"] = 1; //сколько ссылок показывать в начале и в конце
  $a["prev_text"] = "← Пред."; //текст ссылки "Предыдущая страница"
  $a["next_text"] = "След. →"; //текст ссылки "Следующая страница"
  $a["prev_next"] = false;
  if ($max > 1)  {
    $prev = get_previous_posts_link(__("", "oxboxwise"));
    $next = get_next_posts_link(__("", "oxboxwise"));
    if ($prev) {
      $prev = str_replace("href=", "class=\"pagination__item prev-page\" href=", $prev);
      echo $prev;
    }
    $links = paginate_links($a);
    $links = preg_replace( '~/page/1/?([\'"])~', '\1', $links );
    $links = str_replace("page-numbers", "pagination__item page", $links);
    $links = str_replace("span", "a", $links);
    echo $links;
    if ($next) {
      $next = str_replace("href=", "class=\"pagination__item next-page\" href=", $next);
      echo $next;
    }
  }
}
