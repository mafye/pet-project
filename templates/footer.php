        </main>
        <footer>
            <p>&copy; <?php echo date('Y'); ?> 我的虚拟宠物. 保留所有权利.</p>
        </footer>
    </div>
    
    <?php if (isset($extra_js)): ?>
        <?php foreach ($extra_js as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
