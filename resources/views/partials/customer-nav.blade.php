<nav class="sidebar" id="sidebar">
    <button class="sidebar-toggle" onclick="toggleSidebar()">
        <span></span>
        <span></span>
        <span></span>
    </button>
    
    <div class="sidebar-content">
        <div class="sidebar-header">
            <h3>Customer</h3>
        </div>
        
        <div class="sidebar-menu">
            <!-- Add your menu items here -->
        </div>
    </div>
</nav>

<style>
.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    height: 100vh;
    width: 250px;
    background: #1B4D3E;
    transition: transform 0.3s ease;
    z-index: 1000;
}

.sidebar.collapsed {
    transform: translateX(-250px);
}

.sidebar-toggle {
    position: absolute;
    right: -40px;
    top: 20px;
    width: 40px;
    height: 40px;
    background: #C5A059;
    border: none;
    border-radius: 0 8px 8px 0;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 4px;
}

.sidebar-toggle span {
    width: 20px;
    height: 2px;
    background: #1B4D3E;
    transition: 0.3s;
}

.sidebar-content {
    padding: 20px;
    color: white;
}

.sidebar-header h3 {
    color: #C5A059;
    margin: 0;
    font-size: 1.5rem;
}

.sidebar-menu {
    margin-top: 30px;
}
</style>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('collapsed');
}
</script>
