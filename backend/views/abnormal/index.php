<?php
?>
<div class="x-body">
    <table class="layui-table">
        <thead>
            <th>CARD NAME</th>
            <th>TIME</th>
            <th>刷卡门</th>
            
        </thead>
        <tbody>
        <?php foreach($data as $list): ?>
            <tr>
                 <td><?= $list['card_name'] ?></td>
                 <td><?= $list['event_time'] ?></td>
                 <td><?= $list['reader'] ?></td>
           </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?= $this->render('@app/views/layouts/pagination', compact('pagination')) ?>
</div>
<?php $this->beginBlock('footer') ?>
<script>
   function myrefresh()
    {
        window.location.reload();
    }
    setTimeout('myrefresh()',3000); //指定3秒刷新一次
</script>
<?php $this->endBlock() ?>



