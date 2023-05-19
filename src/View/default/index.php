<?= $this->start('content') ?>
    <ul class="list-group mt-5">
        <?php foreach ($this->_vp->users as $user): ?>
            <li class="list-group-item">
                <?= $user->name ?>
                <a href="/info?id=<?= $user->id ?>">Edit</a>
                <a class="js-delete" href="/delete?id=<?= $user->id ?>">Delete</a>
            </li>
        <?php endforeach; ?>
    </ul>
    <div class="card mt-5">
        <form class="card-body" method="post">
            <div class="mb-3">
                <label for="user_name" class="form-label">Name</label>
                <input type="text" class="form-control" id="user_name" name="name">
            </div>
            <div class="mb-3">
                <button class="btn btn-success" type="submit">
                    Save
                </button>
            </div>
        </form>
    </div>
<?= $this->end() ?>

<?= $this->start('javascript') ?>
    <script>
        let deleteButtons = document.querySelectorAll('.js-delete');
        deleteButtons.forEach((button) => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                if (confirm('Delete object?')) {
                    document.location.href = e.target.getAttribute('href');
                }
            });
        });
    </script>
<?= $this->end() ?>
