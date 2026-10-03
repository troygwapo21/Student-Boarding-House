<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><?= isset($amenity) ? 'Edit Amenity' : 'Create Amenity' ?></h1>
    <a href="<?= url('/manager/amenities') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= url(isset($amenity) ? '/manager/amenity/edit/' . $amenity['id'] : '/manager/amenity/create') ?>">
            <?= csrf_field() ?>
            <?php if (!empty($amenity)): ?>
                <input type="hidden" name="id" value="<?= $amenity['id'] ?>">
            <?php endif; ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?= e($amenity['name'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= ($amenity['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($amenity['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?= e($amenity['description'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Icon <span class="text-danger">*</span></label>
                    <input type="hidden" name="icon" id="iconInput" value="<?= e($amenity['icon'] ?? '') ?>">
                    <div class="d-flex align-items-center mb-3">
                        <div id="selectedPreview" class="d-flex align-items-center justify-content-center rounded-circle me-3" style="width:56px;height:56px;background:#eff6ff;border:2px solid #2563eb;min-width:56px;">
                            <?php if (!empty($amenity['icon'])): ?>
                                <i class="<?= e($amenity['icon']) ?> fa-lg" style="color:#2563eb;"></i>
                            <?php else: ?>
                                <i class="fas fa-check fa-lg text-muted"></i>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div id="selectedLabel" class="fw-bold small"><?= e($amenity['icon'] ?? 'No icon selected') ?></div>
                            <small class="text-muted">Click an icon below to select</small>
                        </div>
                    </div>
                    <input type="text" id="iconSearch" class="form-control form-control-sm mb-2" placeholder="Search icons...">
                    <div id="iconGrid" class="border rounded p-2" style="max-height:220px;overflow-y:auto;background:#f8f9fa;">
                        <div class="row g-1" id="iconList"></div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i><?= isset($amenity) ? 'Update' : 'Create' ?> Amenity</button>
                <a href="<?= url('/manager/amenities') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
(function(){
    var icons = [
        {cls:'fas fa-wifi',label:'Wi-Fi'},{cls:'fas fa-snowflake',label:'Air Conditioning'},{cls:'fas fa-desktop',label:'Study Desk'},
        {cls:'fas fa-door-closed',label:'Wardrobe'},{cls:'fas fa-bath',label:'Bathroom'},{cls:'fas fa-shower',label:'Shower'},
        {cls:'fas fa-temperature-high',label:'Hot Water'},{cls:'fas fa-shirt',label:'Laundry'},{cls:'fas fa-utensils',label:'Kitchen'},
        {cls:'fas fa-couch',label:'Common Area'},{cls:'fas fa-shield-halved',label:'Security'},{cls:'fas fa-video',label:'CCTV'},
        {cls:'fas fa-square-parking',label:'Parking'},{cls:'fas fa-droplet',label:'Water'},{cls:'fas fa-bolt',label:'Generator'},
        {cls:'fas fa-broom',label:'Cleaning'},{cls:'fas fa-bed',label:'Bed Linens'},{cls:'fas fa-plug',label:'Electrical Outlet'},
        {cls:'fas fa-person-shelter',label:'Balcony'},{cls:'fas fa-tv',label:'Television'},{cls:'fas fa-blender',label:'Blender'},
        {cls:'fas fa-mug-hot',label:'Hot Drinks'},{cls:'fas fa-fan',label:'Fan'},{cls:'fas fa-fire',label:'Heater'},
        {cls:'fas fa-key',label:'Key Access'},{cls:'fas fa-bell',label:'Bell/Alert'},{cls:'fas fa-phone',label:'Phone'},
        {cls:'fas fa-print',label:'Printer'},{cls:'fas fa-book',label:'Library'},{cls:'fas fa-dumbbell',label:'Gym'},
        {cls:'fas fa-swimming-pool',label:'Pool'},{cls:'fas fa-tree',label:'Garden'},{cls:'fas fa-shirt',label:'Clothes Rack'},
        {cls:'fas fa-utensil-spoon',label:'Utensils'},{cls:'fas fa-sink',label:'Sink'},{cls:'fas fa-fire-extinguisher',label:'Fire Extinguisher'},
        {cls:'fas fa-smoking',label:'Smoking Area'},{cls:'fas fa-paw',label:'Pet Friendly'},{cls:'fas fa-baby',label:'Child Friendly'},
        {cls:'fas fa-wheelchair',label:'Accessible'},{cls:'fas fa-elevator',label:'Elevator'},{cls:'fas fa-stairs',label:'Stairs'},
        {cls:'fas fa-box',label:'Storage'},{cls:'fas fa-recycle',label:'Recycling'},{cls:'fas fa-trash',label:'Trash Bin'},
        {cls:'fas fa-solar-panel',label:'Solar'},{cls:'fas fa-wind',label:'Ventilation'},{cls:'fas fa-clock',label:'24-Hour'},
        {cls:'fas fa-lock',label:'Lock'},{cls:'fas fa-wallet',label:'Payment'},{cls:'fas fa-id-card',label:'ID Card'}
    ];
    var iconInput = document.getElementById('iconInput');
    var preview = document.getElementById('selectedPreview');
    var label = document.getElementById('selectedLabel');
    var search = document.getElementById('iconSearch');
    var list = document.getElementById('iconList');
    var current = iconInput.value;

    function render(filter){
        list.innerHTML = '';
        var f = (filter || '').toLowerCase();
        icons.forEach(function(ic){
            if (f && ic.label.toLowerCase().indexOf(f) === -1 && ic.cls.toLowerCase().indexOf(f) === -1) return;
            var col = document.createElement('div');
            col.className = 'col-auto';
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-sm d-inline-flex align-items-center justify-content-center icon-pick';
            btn.style.cssText = 'width:42px;height:42px;border-radius:8px;border:2px solid '+(ic.cls===current?'#2563eb':'#dee2e6')+';background:'+(ic.cls===current?'#eff6ff':'#fff')+';transition:all .15s;';
            btn.title = ic.label;
            btn.innerHTML = '<i class="'+ic.cls+'"></i>';
            btn.setAttribute('data-cls', ic.cls);
            btn.setAttribute('data-label', ic.label);
            btn.addEventListener('click', function(){
                var cls = this.getAttribute('data-cls');
                iconInput.value = cls;
                current = cls;
                preview.innerHTML = '<i class="'+cls+' fa-lg" style="color:#2563eb;"></i>';
                label.textContent = ic.label + '  (' + cls + ')';
                render(search.value);
            });
            btn.addEventListener('mouseenter', function(){ this.style.transform='scale(1.15)'; this.style.borderColor='#2563eb'; });
            btn.addEventListener('mouseleave', function(){ this.style.transform=''; this.style.borderColor= this.getAttribute('data-cls')===current?'#2563eb':'#dee2e6'; });
            col.appendChild(btn);
            list.appendChild(col);
        });
    }
    search.addEventListener('input', function(){ render(this.value); });
    render('');
})();
</script>

<style>
.icon-pick:hover { transform:scale(1.15); }
</style>
