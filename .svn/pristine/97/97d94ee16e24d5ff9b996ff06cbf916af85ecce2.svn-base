<?php if (!defined('THINK_PATH')) exit(); if(is_array($tree)): $i = 0; $__LIST__ = $tree;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$list): $mod = ($i % 2 );++$i;?><dl class="cate-item">
		<dt class="cf">
			<form action="<?php echo U('add'.$model);?>" method="post">
				<div class="btn-toolbar opt-btn cf">
					<a title="编辑" href="<?php echo U('add'.$model.'?id='.$list['id'].'&pid='.$list['pid']);?>">编辑</a>
					<a title="<?php echo (show_status_op($list["status"])); ?>" href="<?php echo U('set'.$model.'Status?ids='.$list['id'].'&status='.abs(1-$list['status']));?>" class="ajax-get"><?php echo (show_status_op($list["status"])); ?></a>
					<a title="删除" href="<?php echo U('set'.$model.'Status?ids='.$list['id'].'&status=-1');?>" class="confirm ajax-get">删除</a>
					<?php if(($canMove) == "true"): ?><a title="移动" href="<?php echo U('operate'.$model.'?type=move&from='.$list['id']);?>">移动</a><?php endif; ?>
                    <?php if(($canMerge) == "true"): ?><a title="合并" href="<?php echo U('operate'.$model.'?type=merge&from='.$list['id']);?>">合并</a><?php endif; ?>

				</div>
				<div class="fold"><i></i></div>
				<div class="order"><input type="text" name="sort" class="text input-mini" value="<?php echo ($list["sort"]); ?>"></div>

				<div class="name">
					<span class="tab-sign"></span>
					<input type="hidden" name="id" value="<?php echo ($list["id"]); ?>">
					<input type="text" name="title" class="text" value="<?php echo ($list["title"]); ?>">
                    <?php if($level > 0): ?><a class="add-sub-cate" title="添加子分类" href="<?php echo U('add'.$model.'?pid='.$list['id']);?>">
                            <i class="icon-add"></i>
                        </a><?php endif; ?>

					<span class="help-inline msg"></span>
				</div>
			</form>
		</dt>

		<?php if(!empty($list['_'])): ?><dd>
                <?php $tree_list = new Admin\Builder\AdminTreeListBuilder(); $tree_list->setLevel($level); $tree_list->setModel($model); $tree_list->tree($list['_']); ?>
			</dd><?php endif; ?>
	</dl><?php endforeach; endif; else: echo "" ;endif; ?>