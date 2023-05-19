<?= $this->start('content') ?>
    <span class="h1 w-100 text-center d-block" style="font-size: 124px;">
        <?= $this->_vp->code ?>
    </span>
    <hr>
    <div class="alert alert-danger m-5 text-center" style="font-size: 24px;">
        <?= $this->_vp->message ?>
    </div>
<?= $this->end() ?>
