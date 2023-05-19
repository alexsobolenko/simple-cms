<?= $this->start('content') ?>
    <div class="card mt-5">
        <div class="card-header">
            <?= $this->_vp->user->id ?> - <?= $this->_vp->user->createdAt->format('d-m-Y') ?>
        </div>
        <form class="card-body" method="post" action="/?id=<?= $this->_vp->user->id ?>">
            <div class="mb-3">
                <label for="user_name" class="form-label">Name</label>
                <input type="text" class="form-control" id="user_name" name="name" value="<?= $this->_vp->user->name ?>">
            </div>
            <div class="mb-3">
                <button class="btn btn-success" type="submit">
                    Save
                </button>
            </div>
        </form>
    </div>
<?= $this->end() ?>
