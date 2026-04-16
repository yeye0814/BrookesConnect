// js/app.js - 主应用逻辑
class BrookesConnectApp {
    constructor() {
        this.api = window.apiClient;
        this.currentSection = 'dashboard';
        this.init();
    }
    
    async init() {
        console.log('BrookesConnect应用初始化...');
        
        // 检测API端点
        await this.api.detectWorkingEndpoint();
        
        // 加载初始数据
        await this.loadDashboard();
        
        // 隐藏加载提示
        document.getElementById('loading').style.display = 'none';
        
        // 显示仪表板
        this.showSection('dashboard');
        
        console.log('应用初始化完成');
    }
    
    async loadDashboard() {
        try {
            const data = await this.api.request('status');
            
            if (data.status === 'success') {
                // 更新统计数据
                if (data.statistics) {
                    this.updateStats(data.statistics);
                }
                
                // 更新页面标题
                document.title = `BrookesConnect - ${data.statistics?.students || 0} Students`;
            }
        } catch (error) {
            console.error('加载仪表板失败:', error);
        }
    }
    
    updateStats(stats) {
        const elements = {
            'student-count': stats.students,
            'society-count': stats.societies,
            'match-count': stats.matches
        };
        
        for (const [id, value] of Object.entries(elements)) {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value;
            }
        }
    }
    
    async showSection(sectionId) {
        this.currentSection = sectionId;
        
        // 更新导航激活状态
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        
        // 显示对应的内容
        const mainContent = document.getElementById('main-content');
        
        switch(sectionId) {
            case 'dashboard':
                mainContent.innerHTML = this.getDashboardHTML();
                await this.loadDashboardData();
                break;
                
            case 'students':
                mainContent.innerHTML = this.getStudentsHTML();
                await this.loadStudents();
                break;
                
            case 'societies':
                mainContent.innerHTML = this.getSocietiesHTML();
                await this.loadSocieties();
                break;
                
            case 'matches':
                mainContent.innerHTML = this.getMatchesHTML();
                await this.loadStudentOptions();
                break;
                
            case 'analytics':
                mainContent.innerHTML = this.getAnalyticsHTML();
                await this.loadAnalytics();
                break;
                
            case 'api-test':
                mainContent.innerHTML = this.getAPITestHTML();
                break;
        }
        
        // 更新导航激活状态
        const activeLink = document.querySelector(`.nav-link[onclick*="${sectionId}"]`);
        if (activeLink) {
            activeLink.classList.add('active');
        }
    }
    
    // 各个区域的HTML模板
    getDashboardHTML() {
        return `
            <section class="hero-section">
                <div class="hero-content">
                    <h1>Connect with Peers at Oxford Brookes University</h1>
                    <p class="hero-subtitle">Find study partners, join societies, and build meaningful connections based on shared interests and academic goals.</p>
                    <div class="hero-stats">
                        <div class="stat-card">
                            <i class="fas fa-user-graduate"></i>
                            <div>
                                <h3 id="student-count">0</h3>
                                <p>Active Students</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-people-group"></i>
                            <div>
                                <h3 id="society-count">0</h3>
                                <p>Societies</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-handshake"></i>
                            <div>
                                <h3 id="match-count">0</h3>
                                <p>Successful Matches</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="hero-image">
                    <i class="fas fa-network-wired"></i>
                </div>
            </section>
            
            <section class="section">
                <div class="section-header">
                    <h2><i class="fas fa-info-circle"></i> Platform Status</h2>
                    <p>Current system status and quick actions</p>
                </div>
                <div class="status-cards" id="status-cards">
                    <div class="loading">Loading platform status...</div>
                </div>
            </section>
        `;
    }
    
    async loadDashboardData() {
        // 加载额外数据
        const statusCards = document.getElementById('status-cards');
        if (statusCards) {
            try {
                const data = await this.api.request('analytics');
                
                if (data.status === 'success' && data.analytics) {
                    let html = '<div class="analytics-grid">';
                    
                    data.analytics.forEach(item => {
                        html += `
                            <div class="analytics-card">
                                <i class="${item.icon || 'fas fa-chart-bar'}"></i>
                                <h4>${item.metric}</h4>
                                <div class="analytics-value">${item.value}</div>
                            </div>
                        `;
                    });
                    
                    html += '</div>';
                    statusCards.innerHTML = html;
                }
            } catch (error) {
                statusCards.innerHTML = '<p class="error">Failed to load analytics</p>';
            }
        }
    }
    
    getStudentsHTML() {
        return `
            <section class="section">
                <div class="section-header">
                    <h2><i class="fas fa-users"></i> Student Directory</h2>
                    <p>Browse and connect with students from Oxford Brookes University</p>
                </div>
                <div class="controls">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="search-students" placeholder="Search students by name, faculty, or major...">
                    </div>
                    <div class="filters">
                        <select id="faculty-filter">
                            <option value="">All Faculties</option>
                        </select>
                        <select id="level-filter">
                            <option value="">All Levels</option>
                            <option value="Undergraduate">Undergraduate</option>
                            <option value="Postgraduate">Postgraduate</option>
                        </select>
                    </div>
                </div>
                <div class="students-grid" id="students-container">
                    <div class="loading">Loading students...</div>
                </div>
            </section>
        `;
    }
    
    async loadStudents() {
        const container = document.getElementById('students-container');
        if (!container) return;
        
        try {
            const data = await this.api.request('students');
            
            if (data.status === 'success' && data.data) {
                let html = '';
                
                data.data.forEach(student => {
                    html += `
                        <div class="student-card">
                            <div class="student-header">
                                <div class="student-avatar">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <div>
                                    <h3>${student.nickname || 'Unknown'}</h3>
                                    <p class="faculty">${student.faculty || 'Not specified'}</p>
                                </div>
                            </div>
                            <div class="student-details">
                                <div class="detail">
                                    <i class="fas fa-graduation-cap"></i>
                                    ${student.academic_level || 'N/A'}
                                </div>
                                <div class="detail">
                                    <i class="fas fa-book"></i>
                                    ${student.major || 'Undeclared'}
                                </div>
                                <div class="detail">
                                    <i class="fas fa-hashtag"></i>
                                    ID: ${student.student_id}
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                container.innerHTML = html;
                
                // 设置搜索和过滤事件
                this.setupStudentFilters(data.faculties);
            }
        } catch (error) {
            container.innerHTML = '<div class="error">Failed to load students</div>';
        }
    }
    
    setupStudentFilters(faculties) {
        // 填充学院过滤选项
        const facultyFilter = document.getElementById('faculty-filter');
        if (facultyFilter && faculties) {
            faculties.forEach(faculty => {
                const option = document.createElement('option');
                option.value = faculty;
                option.textContent = faculty;
                facultyFilter.appendChild(option);
            });
        }
        
        // 设置搜索事件
        const searchInput = document.getElementById('search-students');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.filterStudents(e.target.value);
            });
        }
    }
    
    filterStudents(searchTerm) {
        // 实现搜索过滤逻辑
        console.log('Searching for:', searchTerm);
    }
    
    // 其他方法类似实现...
    
    getAPITestHTML() {
        return `
            <section class="section">
                <div class="section-header">
                    <h2><i class="fas fa-code"></i> API Testing Interface</h2>
                    <p>Test the BrookesConnect API endpoints</p>
                </div>
                <div class="api-controls">
                    <button class="api-btn" onclick="testEndpoint('status')">
                        <i class="fas fa-server"></i> System Status
                    </button>
                    <button class="api-btn" onclick="testEndpoint('students')">
                        <i class="fas fa-users"></i> Get Students
                    </button>
                    <button class="api-btn" onclick="testEndpoint('societies')">
                        <i class="fas fa-people-group"></i> Get Societies
                    </button>
                    <button class="api-btn" onclick="testEndpoint('analytics')">
                        <i class="fas fa-chart-bar"></i> Get Analytics
                    </button>
                    <button class="api-btn" onclick="testEndpoint('matches')">
                        <i class="fas fa-heart"></i> Get Matches
                    </button>
                </div>
                <div class="api-result">
                    <div class="api-result-header">
                        <h3><i class="fas fa-terminal"></i> API Response</h3>
                        <button onclick="clearResult()" class="btn-clear">
                            <i class="fas fa-trash"></i> Clear
                        </button>
                    </div>
                    <pre id="api-result-content">Click a button above to test an endpoint</pre>
                </div>
            </section>
        `;
    }
}

// 全局函数供按钮调用
async function testEndpoint(action) {
    const resultContent = document.getElementById('api-result-content');
    if (!resultContent) return;
    
    resultContent.textContent = 'Requesting...';
    
    try {
        const data = await window.apiClient.request(action);
        resultContent.textContent = JSON.stringify(data, null, 2);
    } catch (error) {
        resultContent.textContent = `Error: ${error.message}`;
    }
}

function clearResult() {
    const resultContent = document.getElementById('api-result-content');
    if (resultContent) {
        resultContent.textContent = 'Click a button above to test an endpoint';
    }
}

// 页面加载时初始化应用
document.addEventListener('DOMContentLoaded', () => {
    window.app = new BrookesConnectApp();
    
    // 全局函数
    window.showSection = (section) => window.app.showSection(section);
    window.testEndpoint = testEndpoint;
    window.clearResult = clearResult;
});