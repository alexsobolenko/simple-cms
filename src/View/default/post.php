<?= $this->start('content') ?>
    <ul class="list-group mt-5">
        <li class="list-group-item list-group-item-info"><b>ID:</b> <?= $this->_vp->user->id ?></li>
        <li class="list-group-item"><b>Name:</b> <?= $this->_vp->user->name ?></li>
        <li class="list-group-item"><b>Created at:</b> <?= $this->_vp->user->createdAt->format('d-m-Y') ?></li>
    </ul>
<?= $this->end() ?>
