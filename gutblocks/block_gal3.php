<?php if( have_rows('block_gal3_rep')){?>
<section class="coverings" id="block_<?=$block['id'];?>">
    <div class="container">
        <div class="coverings-application__wrapper">
            <? if(get_field('block_gal3_tit')){?>
                <h2><?=get_field('block_gal3_tit');?></h2>
            <? }?>
            <div class="coverings-application">
                <?  while ( have_rows('block_gal3_rep') ) : the_row();
                $block_gal3_rep_img = get_sub_field('block_gal3_rep_img');
                $block_gal3_rep_tit = get_sub_field('block_gal3_rep_tit');
                ?>
                    <? if($block_gal3_rep_img){?>
                        <div class="coverings-application__item">
                            <img src="<?=$block_gal3_rep_img['sizes']['wise_over'];?>" alt="<?=$block_gal3_rep_img['alt'];?>">
                            <? if($block_gal3_rep_tit){?>
                            <div class="coverings-application__item-name"><?=$block_gal3_rep_tit;?></div>
                            <? }?>
                        </div>
                    <? }?>
                <? endwhile;?>
            </div>
        </div>
    </div>
</section>
<?php }?>
