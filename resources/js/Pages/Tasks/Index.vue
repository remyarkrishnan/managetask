<template>
  <div class="min-h-screen bg-[#0f172a] text-slate-100 font-sans antialiased p-4 sm:p-6 md:p-8">
    <div class="max-w-7xl mx-auto">
      
      <!-- Top header bar with app branding and welcome -->
      <header class="flex justify-between items-center mb-8 border-b border-slate-800 pb-4">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
            <span class="text-blue-500 font-extrabold">AI</span> Task Flow
          </h1>
          <p class="text-xs text-slate-400 mt-1">Clean Architecture & AI Assisted Task Management</p>
        </div>
        <div class="flex items-center gap-4">
          <span class="text-xs px-2.5 py-1 rounded-full bg-slate-800 border border-slate-700 text-slate-300">
            Role: <span class="capitalize font-semibold text-blue-400">{{ $page.props.auth.user.role }}</span>
          </span>
          <div class="text-sm font-medium text-slate-300">
            Welcome, {{ $page.props.auth.user.name }}
          </div>
        </div>
      </header>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Panel: Content Area (List, Detail, Create or Edit) -->
        <main class="lg:col-span-2 space-y-6">
          
          <!-- Heading Area -->
          <div class="flex justify-between items-center">
            <h2 class="text-3xl font-extrabold text-white tracking-tight flex items-center gap-4">
              <button 
                v-if="currentView === 'user_tasks'" 
                @click="backToUsers"
                class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg text-sm font-semibold flex items-center gap-2 transition-colors border border-slate-700"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back
              </button>
              <span v-if="currentView === 'list'">Task List</span>
              <span v-else-if="currentView === 'user_tasks'">Tasks: {{ viewingUser?.name }}</span>
              <span v-else-if="currentView === 'users'">System Users</span>
              <span v-else-if="currentView === 'detail'">Task Detail + AI Summary</span>
              <span v-else-if="currentView === 'edit'">Edit Task</span>
              <span v-else-if="currentView === 'create'">Create Task</span>
            </h2>
            <!-- "New Task" button always visible or conditional based on view -->
            <button 
              v-if="currentView !== 'create' && $page.props.auth.user.role === 'admin'"
              @click="openCreate"
              class="px-5 py-2.5 bg-blue-500 hover:bg-blue-400 active:bg-blue-600 text-white font-bold rounded-lg shadow transition-colors text-sm flex items-center gap-2"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
              </svg>
              New Task
            </button>
          </div>

          <!-- Filters bar (Visible in list and detail views as per mockup) -->
          <div v-if="['list', 'detail', 'user_tasks'].includes(currentView)" class="flex flex-col md:flex-row gap-4 items-center">
            <div class="relative w-full md:w-64">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <input 
                type="text" 
                v-model="searchQuery"
                @input="applyFilters"
                placeholder="Search Filter Task" 
                class="w-full bg-white border border-slate-200 rounded-lg pl-9 pr-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 shadow-sm"
              />
            </div>

            <div class="flex flex-wrap w-full md:w-auto items-center gap-3">
              <select 
                v-model="selectedStatus" 
                @change="applyFilters"
                class="bg-white border border-slate-200 rounded-lg px-4 py-2.5 text-sm text-slate-600 focus:outline-none focus:border-blue-500 shadow-sm w-full sm:w-auto"
              >
                <option value="all">Status</option>
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
              </select>

              <select 
                v-model="selectedAssignee" 
                @change="applyFilters"
                class="bg-white border border-slate-200 rounded-lg px-4 py-2.5 text-sm text-slate-600 focus:outline-none focus:border-blue-500 shadow-sm w-full sm:w-auto"
              >
                <option value="all">All Assignees</option>
                <option v-for="user in users.data" :key="user.id" :value="user.id">
                  {{ user.name }}
                </option>
              </select>

              <select 
                v-model="selectedPriority" 
                @change="applyFilters"
                class="bg-white border border-slate-200 rounded-lg px-4 py-2.5 text-sm text-slate-600 focus:outline-none focus:border-blue-500 shadow-sm w-full sm:w-auto"
              >
                <option value="all">Priority</option>
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
              </select>
            </div>
          </div>

          <!-- Active View Panels -->

          <!-- Panel 1: Task List View -->
          <div v-if="currentView === 'list' || currentView === 'user_tasks'" class="space-y-6">
            <div v-if="tasks.data.length === 0" class="bg-slate-900 border border-slate-800 rounded-2xl p-12 text-center shadow-md">
              <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-slate-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
              </svg>
              <h3 class="text-lg font-semibold text-white">No Tasks Found</h3>
              <p class="text-slate-400 mt-1 text-sm">Get started by creating a new task or adjust your search filters.</p>
              <button v-if="$page.props.auth.user.role === 'admin'" @click="openCreate" class="mt-4 px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-medium rounded-lg text-sm transition-colors">
                Create First Task
              </button>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Task Card -->
              <div 
                v-for="task in tasks.data" 
                :key="task.id" 
                class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-shadow flex flex-col h-full"
              >
                <!-- Card Header -->
                <div class="flex justify-between items-center mb-4">
                  <div class="flex items-center gap-2 bg-slate-50 px-3 py-1 rounded-full border border-slate-100">
                    <div class="h-4 w-4 rounded-full bg-[#4385f5] flex items-center justify-center">
                      <svg class="h-2.5 w-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                      </svg>
                    </div>
                    <span class="text-xs font-bold text-slate-600 capitalize">{{ formatStatus(task.status) }}</span>
                  </div>
                  
                  <div class="flex gap-1 items-center">
                    <button 
                      v-if="$page.props.auth.user.role === 'admin'"
                      @click.stop="deleteTask(task.id)"
                      class="text-slate-300 hover:text-red-500 transition-colors mr-2"
                      title="Delete Task"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                    <!-- Static dots menu from mockup -->
                    <span class="text-slate-400 font-black tracking-widest text-lg leading-none -mt-2 cursor-pointer">....</span>
                  </div>
                </div>

                <!-- Task Title -->
                <h3 class="text-[17px] font-extrabold text-[#1f2937] mb-3 leading-snug">{{ task.title }}</h3>

                <!-- Pills Row -->
                <div class="flex flex-wrap gap-2 mb-4">
                  <span class="text-[10px] font-bold px-2.5 py-0.5 rounded border border-slate-200 bg-slate-50 text-slate-500">
                    {{ task.user ? task.user.name.split(' ')[0] : 'Unassigned' }}
                  </span>
                  <span 
                    class="text-[10px] font-bold px-2.5 py-0.5 rounded text-white capitalize"
                    :class="{
                      'bg-[#ef4444]': task.priority === 'high',
                      'bg-[#f59e0b]': task.priority === 'medium',
                      'bg-[#4385f5]': task.priority === 'low'
                    }"
                  >
                    Priority {{ task.priority }}
                  </span>
                </div>

                <!-- Description / Details Container -->
                <div class="flex-grow">
                  <div v-if="task.description" class="bg-[#f8f9fa] p-3 rounded-xl border border-slate-100 mb-4 text-xs leading-relaxed text-slate-500">
                    <p class="font-bold text-slate-700 inline">Description:</p>
                    <span class="line-clamp-2 inline"> {{ task.description }}</span>
                    <p v-if="task.ai_priority" class="mt-1 font-bold text-slate-700">AI Priority: <span class="font-normal capitalize">{{ task.ai_priority }}</span></p>
                  </div>
                  <div v-else class="mb-4 text-xs leading-relaxed text-slate-500 space-y-1.5 px-1">
                    <p><span class="font-semibold">Assignee:</span> {{ task.user ? task.user.name : 'Unassigned' }}</p>
                    <p><span class="font-semibold">Due:</span> {{ task.due_date || 'No date set' }}</p>
                    <p v-if="task.ai_priority"><span class="font-semibold">AI Priority:</span> {{ task.ai_priority }}</p>
                  </div>
                </div>

                <!-- Footer Actions -->
                <div class="mt-auto pt-2 flex items-center justify-between">
                  <!-- Bottom left blue text from mockup -->
                  <div class="font-bold text-sm text-[#4385f5] capitalize px-1">{{ task.priority }}</div>
                  
                  <div class="flex gap-2">
                    <button 
                      @click="openEdit(task)"
                      class="px-4 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-full text-xs transition-colors shadow-sm"
                    >
                      Edit
                    </button>
                    <button 
                      @click="openDetail(task)"
                      class="px-4 py-1.5 bg-[#4385f5] hover:bg-blue-600 text-white font-bold rounded-full text-xs transition-colors shadow-sm"
                    >
                      View
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pagination (if pages exist) -->
            <div v-if="tasks.meta && tasks.meta.last_page > 1" class="flex justify-center items-center gap-2 pt-4">
              <button 
                v-for="link in tasks.meta.links" 
                :key="link.label"
                @click="goToPage(link.url)"
                v-html="link.label"
                class="px-3.5 py-2 rounded-lg text-sm font-semibold transition-all border"
                :class="{
                  'bg-blue-600 border-blue-600 text-white': link.active,
                  'bg-slate-900 border-slate-800 text-slate-400 hover:border-slate-700 hover:text-white': !link.active && link.url,
                  'opacity-40 cursor-not-allowed border-slate-900 text-slate-600': !link.url
                }"
                :disabled="!link.url"
              ></button>
            </div>
          </div>

          <!-- Panel: Users List View -->
          <div v-else-if="currentView === 'users'" class="bg-white rounded-2xl border border-slate-200 shadow-lg overflow-hidden text-slate-800">
            <div class="p-6 md:p-8 border-b border-slate-100 bg-[#f8f9fa]">
              <h3 class="text-xl font-bold text-[#1f2937]">Registered Users</h3>
              <p class="text-sm text-slate-500 mt-1">Select a user to view their assigned tasks.</p>
            </div>
            <div class="divide-y divide-slate-100">
              <div 
                v-for="user in users.data" 
                :key="user.id" 
                @click="viewUserTasks(user)"
                class="p-4 md:px-8 md:py-5 flex items-center justify-between hover:bg-slate-50 cursor-pointer transition-colors"
              >
                <div class="flex items-center gap-4">
                  <div class="h-10 w-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                    {{ getInitials(user.name) }}
                  </div>
                  <div>
                    <h4 class="font-bold text-slate-900 text-base">{{ user.name }}</h4>
                    <p class="text-xs text-slate-500">{{ user.email }} • <span class="capitalize">{{ user.role }}</span></p>
                  </div>
                </div>
                <div class="flex items-center gap-4">
                  <div class="text-right">
                    <span class="block text-xl font-black text-[#4385f5]">{{ user.tasks_count || 0 }}</span>
                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Tasks</span>
                  </div>
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </div>
              </div>
            </div>
          </div>

          <!-- Panel 2: Task Detail + AI Summary View (Redesigned per mockup) -->
          <div v-else-if="currentView === 'detail' && selectedTask" class="bg-white rounded-2xl p-6 md:p-8 border border-slate-200 shadow-lg text-slate-800">
            <!-- Header section matching mockup -->
            <div class="mb-6">
              <h3 class="text-[28px] font-bold text-[#1f2937] leading-tight mb-4 tracking-tight">{{ selectedTask.title }}</h3>
              <div class="flex flex-wrap gap-4">
                <span class="text-sm font-semibold text-slate-600 bg-slate-100 rounded-full px-4 py-1.5 flex items-center gap-1.5">
                  Status <span class="text-slate-400 font-normal capitalize">{{ formatStatus(selectedTask.status) }}</span>
                </span>
                <span class="text-sm font-semibold text-slate-600 bg-slate-100 rounded-full px-4 py-1.5 flex items-center gap-1.5">
                  Priority <span class="text-slate-400 font-normal capitalize">{{ selectedTask.priority }}</span>
                </span>
              </div>
            </div>

            <!-- Main Inner Gray Container -->
            <div class="bg-[#f8f9fa] rounded-2xl p-6 md:p-8 space-y-6">
              
              <!-- Description Heading and Assinee -->
              <div>
                <h4 class="text-lg font-bold text-slate-800 mb-4">Description</h4>
                <p class="text-sm font-bold text-slate-800 mb-3">
                  Assigned to: <span class="font-normal text-slate-600">{{ selectedTask.user ? selectedTask.user.name : 'Unassigned' }}</span>
                </p>
                
                <!-- White Due Date Input-like box -->
                <div class="bg-white border border-slate-200 rounded-lg p-3 flex justify-between items-center mb-6">
                  <span class="text-sm text-slate-500">Due Date: {{ selectedTask.due_date || 'Not specified' }}</span>
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 00-2 2z" />
                  </svg>
                </div>

                <p class="text-slate-600 text-sm leading-relaxed whitespace-pre-line mb-6">
                  {{ selectedTask.description || 'No description provided.' }}
                </p>
              </div>

              <!-- AI Generated Summary Blocks -->
              <div class="space-y-4">
                <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
                  <h5 class="text-base font-bold text-slate-800 mb-2">AI-Generated Summary</h5>
                  <p v-if="selectedTask.ai_summary" class="text-sm text-slate-600 leading-relaxed">{{ selectedTask.ai_summary }}</p>
                  <p v-else class="text-sm text-slate-400 italic">No summary generated. Click "Refresh AI Summary" to generate.</p>
                </div>
                
                <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex gap-1 items-center">
                  <span class="font-bold text-slate-800 text-sm">AI Priority:</span>
                  <span class="text-sm text-slate-600 capitalize">{{ selectedTask.ai_priority || 'Not evaluated' }}</span>
                </div>
              </div>

              <!-- Save Changes / Close Button -->
              <div class="pt-6 flex justify-center">
                <button 
                  @click="backToList"
                  class="px-8 py-2.5 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-full transition-colors text-sm shadow-sm"
                >
                  Close Task Details
                </button>
              </div>
            </div>
          </div>

          <!-- Panel 3 & 4: Create / Edit Task Form (Redesigned per mockup) -->
          <div v-else-if="(currentView === 'create' || currentView === 'edit') && taskForm" class="bg-white rounded-2xl p-6 md:p-8 border border-slate-200 shadow-lg text-slate-800">
            
            <h3 v-if="currentView === 'edit'" class="text-[28px] font-bold text-[#1f2937] leading-tight mb-6 tracking-tight">
              {{ taskForm.title || 'Edit Task' }}
            </h3>
            <h3 v-else class="text-[28px] font-bold text-[#1f2937] leading-tight mb-6 tracking-tight">
              Create New Task
            </h3>

            <!-- Main Inner Gray Container -->
            <form @submit.prevent="submitTaskForm" class="bg-[#f8f9fa] rounded-2xl p-6 md:p-8 space-y-6 relative">
              
              <!-- Title Input -->
              <div>
                <div class="relative">
                  <input 
                    type="text" 
                    id="title" 
                    v-model="taskForm.title" 
                    required
                    placeholder="e.g. Launch New Campaign"
                    class="w-full bg-white border border-slate-200 rounded-lg px-4 py-3.5 text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#4385f5] shadow-sm text-sm"
                  />
                  <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                    <div class="h-6 w-6 rounded-full bg-slate-200 overflow-hidden">
                      <img v-if="$page.props.auth.user.avatar" :src="$page.props.auth.user.avatar" class="h-full w-full object-cover">
                      <svg v-else class="h-full w-full text-slate-400" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </div>
                  </div>
                </div>
                <p v-if="taskForm?.errors?.title" class="mt-1 text-xs text-red-500 px-1">{{ taskForm.errors.title }}</p>
              </div>

              <!-- Description Textarea -->
              <div>
                <textarea 
                  id="description" 
                  v-model="taskForm.description" 
                  rows="4" 
                  placeholder="Task Description..."
                  class="w-full bg-white border border-slate-200 rounded-lg p-4 text-slate-600 text-sm leading-relaxed placeholder-slate-400 focus:outline-none focus:border-[#4385f5] shadow-sm resize-none"
                ></textarea>
                <p v-if="taskForm?.errors?.description" class="mt-1 text-xs text-red-500 px-1">{{ taskForm.errors.description }}</p>
              </div>

              <!-- Status & Priority -->
              <div class="flex flex-col xl:flex-row gap-6 items-start xl:items-center w-full">
                
                <!-- Priority Selector (Matches mockup) -->
                <div class="flex items-center gap-4 w-full xl:flex-1">
                  <label class="text-base font-bold text-slate-800 w-16 shrink-0">Priority</label>
                  <div class="bg-white rounded-lg p-1 border border-slate-200 shadow-sm w-full">
                    <div class="relative flex w-full">
                      <!-- Sliding Background -->
                      <div 
                        class="absolute top-0 bottom-0 left-0 w-1/3 bg-[#4385f5] rounded-md shadow-sm transition-transform duration-300 ease-out"
                        :class="{
                          'translate-x-0': taskForm.priority === 'low',
                          'translate-x-full': taskForm.priority === 'medium',
                          'translate-x-[200%]': taskForm.priority === 'high'
                        }"
                      ></div>
                      <!-- Buttons -->
                      <button type="button" @click="taskForm.priority = 'low'" :class="taskForm.priority === 'low' ? 'text-white' : 'text-slate-500 hover:text-slate-700'" class="relative z-10 flex-1 py-2.5 text-sm font-semibold transition-colors duration-300 text-center">Low</button>
                      <button type="button" @click="taskForm.priority = 'medium'" :class="taskForm.priority === 'medium' ? 'text-white' : 'text-slate-500 hover:text-slate-700'" class="relative z-10 flex-1 py-2.5 text-sm font-semibold transition-colors duration-300 text-center">Medium</button>
                      <button type="button" @click="taskForm.priority = 'high'" :class="taskForm.priority === 'high' ? 'text-white' : 'text-slate-500 hover:text-slate-700'" class="relative z-10 flex-1 py-2.5 text-sm font-semibold transition-colors duration-300 text-center">High</button>
                    </div>
                  </div>
                </div>
                
                <!-- Status Selector -->
                <div class="flex items-center gap-4 w-full xl:flex-1">
                  <label class="text-base font-bold text-slate-800 w-16 xl:w-auto shrink-0">Status</label>
                  <div class="bg-white rounded-lg p-1 border border-slate-200 shadow-sm w-full">
                    <div class="relative flex w-full">
                      <!-- Sliding Background -->
                      <div 
                        class="absolute top-0 bottom-0 left-0 w-1/3 bg-slate-800 rounded-md shadow-sm transition-transform duration-300 ease-out"
                        :class="{
                          'translate-x-0': taskForm.status === 'pending',
                          'translate-x-full': taskForm.status === 'in_progress',
                          'translate-x-[200%]': taskForm.status === 'completed'
                        }"
                      ></div>
                      <!-- Buttons -->
                      <button type="button" @click="taskForm.status = 'pending'" :class="taskForm.status === 'pending' ? 'text-white' : 'text-slate-500 hover:text-slate-700'" class="relative z-10 flex-1 py-2.5 text-sm font-semibold transition-colors duration-300 text-center">Pending</button>
                      <button type="button" @click="taskForm.status = 'in_progress'" :class="taskForm.status === 'in_progress' ? 'text-white' : 'text-slate-500 hover:text-slate-700'" class="relative z-10 flex-1 py-2.5 text-sm font-semibold transition-colors duration-300 text-center">In Progress</button>
                      <button type="button" @click="taskForm.status = 'completed'" :class="taskForm.status === 'completed' ? 'text-white' : 'text-slate-500 hover:text-slate-700'" class="relative z-10 flex-1 py-2.5 text-sm font-semibold transition-colors duration-300 text-center">Completed</button>
                    </div>
                  </div>
                </div>

              </div>

              <!-- Due Date -->
              <div>
                <div class="relative w-full md:w-2/3 lg:w-1/2">
                  <input 
                    type="date" 
                    id="due_date" 
                    v-model="taskForm.due_date" 
                    class="w-full bg-white border border-slate-200 rounded-lg px-4 py-3.5 text-slate-600 text-sm focus:outline-none focus:border-[#4385f5] shadow-sm appearance-none"
                  />
                  <!-- Calendar Icon overlay to match mockup -->
                  <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400 bg-white pl-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 00-2 2z" />
                    </svg>
                  </div>
                </div>
                <p v-if="taskForm?.errors?.due_date" class="mt-1 text-xs text-red-500 px-1">{{ taskForm.errors.due_date }}</p>
              </div>

              <!-- Assignee -->
              <div v-if="$page.props.auth.user.role === 'admin'">
                <label class="block text-base font-bold text-slate-800 mb-2">Assign To</label>
                <div class="relative w-full md:w-2/3 lg:w-1/2">
                  <select 
                    id="assigned_to" 
                    v-model="taskForm.assigned_to" 
                    class="w-full bg-white border border-slate-200 rounded-lg px-4 py-3.5 text-slate-600 text-sm focus:outline-none focus:border-[#4385f5] shadow-sm appearance-none"
                  >
                    <option value="">Unassigned</option>
                    <option v-for="user in users.data" :key="user.id" :value="user.id">
                      {{ user.name }}
                    </option>
                  </select>
                  <!-- Caret Icon overlay -->
                  <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400 bg-white pl-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                    </svg>
                  </div>
                </div>
                <p v-if="taskForm?.errors?.assigned_to" class="mt-1 text-xs text-red-500 px-1">{{ taskForm.errors.assigned_to }}</p>
              </div>

              <!-- Save Changes / Cancel Buttons -->
              <div class="pt-6 flex justify-center gap-4">
                <button 
                  type="button" 
                  @click="backToList"
                  class="px-8 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 font-bold rounded-full transition-colors text-sm shadow-sm"
                >
                  Cancel
                </button>
                <button 
                  type="submit" 
                  :disabled="taskForm?.processing"
                  class="px-10 py-2.5 bg-[#4385f5] hover:bg-blue-600 text-white font-bold rounded-full transition-colors text-sm shadow-sm flex items-center gap-2"
                >
                  <svg v-if="taskForm?.processing" class="animate-spin -ml-1 mr-1 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ currentView === 'create' ? 'Create Task' : 'Save Changes' }}
                </button>
              </div>

            </form>
          </div></main>
        
        <!-- Right Panel: Sidebar Stats & Navigation -->
        <aside class="space-y-6">
          
          <!-- Primary White Card: User, Navigation & Counters -->
          <div class="bg-white rounded-2xl p-5 md:p-6 shadow-md border border-slate-200 text-slate-800 space-y-6">
            
            <!-- User Info Section -->
            <div class="flex items-center gap-4">
              <div class="h-12 w-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-lg border border-blue-200">
                {{ getInitials($page.props.auth.user.name) }}
              </div>
              <div>
                <h4 class="font-bold text-slate-900 leading-tight">{{ $page.props.auth.user.name }}</h4>
                <p class="text-xs text-slate-500 capitalize">{{ $page.props.auth.user.role }} User</p>
              </div>
            </div>

            <!-- Navigation Links Redesigned to match mockup -->
            <nav class="space-y-0 border-t border-slate-100 pt-2">
              <button 
                @click="backToList"
                class="w-full flex items-center justify-between px-6 py-3.5 text-sm transition-all text-left"
                :class="currentView === 'list' ? 'bg-[#4385f5] text-white font-semibold' : 'hover:bg-slate-50 text-slate-600 border-b border-slate-100 font-medium'"
              >
                <span>Tasks</span>
                <svg v-if="currentView !== 'list'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                </svg>
              </button>

              <button 
                v-if="$page.props.auth.user.role === 'admin'" 
                @click="openUsersList"
                class="w-full px-6 py-3.5 text-sm flex items-center justify-between border-b border-slate-100 transition-all text-left"
                :class="currentView === 'users' || currentView === 'user_tasks' ? 'text-[#4385f5] font-bold' : 'text-slate-600 font-medium hover:bg-slate-50'"
              >
                <span>Users <span class="text-[10px] text-slate-400 font-normal ml-1" :class="{'text-blue-300': currentView === 'users' || currentView === 'user_tasks'}">(Only visible to Admin)</span></span>
              </button>

              <Link 
                href="/logout" 
                method="post" 
                as="button" 
                class="w-full flex items-center justify-between px-6 py-3.5 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all text-left"
              >
                Logout
              </Link>
            </nav>

            <!-- Radial/Circular stats progress indicators (4 rings side-by-side) -->
            <div class="border-t border-slate-100 pt-5 pb-2">
              <div class="flex items-center justify-center gap-2">
                <!-- Ring 1: Total Tasks -->
                <div class="flex flex-col items-center">
                  <div class="relative h-14 w-14 flex items-center justify-center">
                    <svg class="absolute inset-0 transform -rotate-90 w-full h-full" viewBox="0 0 36 36">
                      <path class="text-slate-100" stroke-width="4" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                      <path class="text-blue-500" stroke-dasharray="100, 100" stroke-width="4" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <span class="text-[11px] font-extrabold text-slate-800">{{ stats.total }}</span>
                  </div>
                  <span class="text-[8px] text-slate-400 font-bold mt-1 text-center truncate w-14">Total</span>
                </div>

                <!-- Ring 2: Completed -->
                <div class="flex flex-col items-center">
                  <div class="relative h-14 w-14 flex items-center justify-center">
                    <svg class="absolute inset-0 transform -rotate-90 w-full h-full" viewBox="0 0 36 36">
                      <path class="text-slate-100" stroke-width="4" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                      <path class="text-emerald-500" :stroke-dasharray="getPercentageString(stats.completed, stats.total)" stroke-width="4" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <span class="text-[11px] font-extrabold text-slate-800">{{ stats.completed }}</span>
                  </div>
                  <span class="text-[8px] text-slate-400 font-bold mt-1 text-center truncate w-14">Completed</span>
                </div>

                <!-- Ring 3: Pending -->
                <div class="flex flex-col items-center">
                  <div class="relative h-14 w-14 flex items-center justify-center">
                    <svg class="absolute inset-0 transform -rotate-90 w-full h-full" viewBox="0 0 36 36">
                      <path class="text-slate-100" stroke-width="4" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                      <path class="text-amber-500" :stroke-dasharray="getPercentageString(stats.pending, stats.total)" stroke-width="4" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <span class="text-[11px] font-extrabold text-slate-800">{{ stats.pending }}</span>
                  </div>
                  <span class="text-[8px] text-slate-400 font-bold mt-1 text-center truncate w-14">Pending</span>
                </div>

                <!-- Ring 4: High Priority -->
                <div class="flex flex-col items-center">
                  <div class="relative h-14 w-14 flex items-center justify-center">
                    <svg class="absolute inset-0 transform -rotate-90 w-full h-full" viewBox="0 0 36 36">
                      <path class="text-slate-100" stroke-width="4" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                      <path class="text-rose-500" :stroke-dasharray="getPercentageString(stats.high_priority, stats.total)" stroke-width="4" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <span class="text-[11px] font-extrabold text-slate-800">{{ stats.high_priority }}</span>
                  </div>
                  <span class="text-[8px] text-slate-400 font-bold mt-1 text-center truncate w-14">High Pr</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Standalone AI Refresh Button per mockup -->
          <button 
            v-if="currentView === 'detail' && selectedTask"
            @click="refreshAiSummary(selectedTask.id)"
            :disabled="refreshingAi"
            class="w-full bg-white rounded-xl py-3.5 px-6 shadow-sm border border-slate-200 flex items-center justify-between text-[#4385f5] font-semibold hover:bg-slate-50 transition-colors"
          >
            <span>{{ refreshingAi ? 'Analyzing...' : 'Refresh AI Summary' }}</span>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" :class="{ 'animate-spin': refreshingAi }">
              <path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8" />
              <path d="M21 3v5h-5" />
            </svg>
          </button>

          <!-- Chart.js Monthly Chart Details -->
          <div class="bg-[#1c2534] rounded-2xl p-5 md:p-6 shadow-md text-white space-y-4 border border-slate-800">
            <h5 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Monthly Task Completion</h5>
            <div class="h-40 w-full">
              <Bar :data="chartData" :options="chartOptions" />
            </div>
          </div>

        </aside>

      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch, computed } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

export default {
  name: 'TaskDashboard',
  components: {
    Link,
    Bar,
  },
  props: {
    tasks: {
      type: Object,
      required: true,
    },
    filters: {
      type: Object,
      required: true,
    },
    stats: {
      type: Object,
      required: true,
    },
    users: {
      type: Object,
      required: true,
    },
  },
  setup(props) {
    // Form and UI States
    const currentView = ref('list'); // 'list', 'detail', 'edit', 'create'
    const selectedTask = ref(null);
    const refreshingAi = ref(false);

    // Filters local states
    const searchQuery = ref(props.filters.search || '');
    const selectedStatus = ref(props.filters.status || 'all');
    const selectedPriority = ref(props.filters.priority || 'all');
    const selectedAssignee = ref(props.filters.assigned_to || 'all');

    // Users List Navigation State
    const viewingUser = ref(null);

    // Task Form
    const taskForm = ref(null);

    // Filter application with throttle/debounce representation
    let filterTimeout;
    const applyFilters = () => {
      clearTimeout(filterTimeout);
      filterTimeout = setTimeout(() => {
        router.get(
          route('tasks.index'),
          {
            search: searchQuery.value,
            status: selectedStatus.value,
            priority: selectedPriority.value,
            assigned_to: selectedAssignee.value,
          },
          {
            preserveState: true,
            replace: true,
          }
        );
      }, 300);
    };

    // Helper functions
    const formatStatus = (status) => {
      return status ? status.replace('_', ' ') : '';
    };

    const getInitials = (name) => {
      if (!name) return 'U';
      return name
        .split(' ')
        .map((n) => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
    };

    const getPercentageString = (value, total) => {
      if (!total || total === 0) return '0, 100';
      const percent = Math.min(100, Math.round((value / total) * 100));
      return `${percent}, 100`;
    };

    const getBarHeight = (value, maxLimit = 20) => {
      const maxVal = Math.max(...Object.values(props.stats.monthly_completed), 1);
      // return a percentage height based on maxVal
      const percent = Math.min(100, Math.round((value / maxVal) * 80) + 10);
      return `${percent}%`;
    };

    // Navigation and View Switches
    const backToList = () => {
      currentView.value = viewingUser.value ? 'user_tasks' : 'list';
      selectedTask.value = null;
      taskForm.value = null;
    };

    const openUsersList = () => {
      currentView.value = 'users';
      viewingUser.value = null;
      selectedAssignee.value = 'all';
      selectedTask.value = null;
    };

    const viewUserTasks = (user) => {
      viewingUser.value = user;
      selectedAssignee.value = user.id;
      currentView.value = 'user_tasks';
      applyFilters();
    };

    const backToUsers = () => {
      viewingUser.value = null;
      selectedAssignee.value = 'all';
      applyFilters();
      currentView.value = 'users';
    };

    const openDetail = (task) => {
      selectedTask.value = task;
      currentView.value = 'detail';
    };

    const openCreate = () => {
      taskForm.value = useForm({
        title: '',
        description: '',
        priority: 'medium',
        status: 'pending',
        due_date: new Date().toISOString().split('T')[0],
        assigned_to: null,
      });
      currentView.value = 'create';
    };

    const openEdit = (task) => {
      selectedTask.value = task;
      taskForm.value = useForm({
        title: task.title,
        description: task.description || '',
        priority: task.priority,
        status: task.status,
        due_date: task.due_date || '',
        assigned_to: task.assigned_to || null,
      });
      currentView.value = 'edit';
    };

    // Form Submissions
    const submitTaskForm = () => {
      if (currentView.value === 'create') {
        taskForm.value.post(route('tasks.store'), {
          onSuccess: () => {
            backToList();
          },
        });
      } else if (currentView.value === 'edit' && selectedTask.value) {
        taskForm.value.put(route('tasks.update', selectedTask.value.id), {
          onSuccess: (page) => {
            // Find updated task in list or props and update detail view if showing
            const updated = page.props.tasks.data.find(t => t.id === selectedTask.value.id);
            if (updated) {
              selectedTask.value = updated;
            }
            backToList();
          },
        });
      }
    };

    // Quick Action: Update Status from detail page
    const updateStatusQuick = (id, newStatus) => {
      router.patch(
        route('tasks.update-status', id),
        { status: newStatus },
        {
          preserveScroll: true,
          onSuccess: (page) => {
            // Update selectedTask details with updated attributes
            const updated = page.props.tasks.data.find(t => t.id === id);
            if (updated) {
              selectedTask.value = updated;
            }
          },
        }
      );
    };

    // Action: Refresh AI Summary
    const refreshAiSummary = (id) => {
      refreshingAi.value = true;
      router.post(
        route('tasks.ai-summary', id),
        {},
        {
          preserveScroll: true,
          onSuccess: (page) => {
            refreshingAi.value = false;
            const updated = page.props.tasks.data.find(t => t.id === id);
            if (updated) {
              selectedTask.value = updated;
            }
          },
          onError: () => {
            refreshingAi.value = false;
          },
        }
      );
    };

    // Action: Delete Task
    const deleteTask = (id) => {
      if (confirm('Are you sure you want to delete this task?')) {
        router.delete(route('tasks.destroy', id), {
          onSuccess: () => {
            backToList();
          },
        });
      }
    };

    // Pagination
    const goToPage = (url) => {
      if (!url) return;
      router.get(
        url,
        {
          search: searchQuery.value,
          status: selectedStatus.value,
          priority: selectedPriority.value,
          assigned_to: selectedAssignee.value,
        },
        {
          preserveState: true,
        }
      );
    };

    // Chart.js data and options
    const chartData = computed(() => ({
      labels: Object.keys(props.stats.monthly_completed || {}),
      datasets: [
        {
          label: 'Completed Tasks',
          backgroundColor: '#4385f5',
          borderRadius: 4,
          data: Object.values(props.stats.monthly_completed || {})
        }
      ]
    }));

    const chartOptions = {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { color: '#94a3b8' }
        },
        y: {
          grid: { color: '#334155' },
          ticks: { stepSize: 1, color: '#94a3b8' },
          beginAtZero: true
        }
      }
    };

    return {
      viewingUser,
      openUsersList,
      viewUserTasks,
      backToUsers,
      currentView,
      selectedTask,
      refreshingAi,
      searchQuery,
      selectedStatus,
      selectedPriority,
      selectedAssignee,
      taskForm,
      applyFilters,
      formatStatus,
      getInitials,
      getPercentageString,
      getBarHeight,
      backToList,
      openDetail,
      openCreate,
      openEdit,
      submitTaskForm,
      updateStatusQuick,
      refreshAiSummary,
      deleteTask,
      goToPage,
      chartData,
      chartOptions,
    };
  },
};
</script>
