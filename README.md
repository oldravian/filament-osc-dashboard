# OSC Admin

OSC Admin is a Laravel-based project designed to manage and display open-source projects. It provides features for importing, categorizing, and managing projects, along with associated media, technologies, and categories. The project integrates with Filament Admin for backend management and includes custom modules for scraping project data and handling media uploads.

## Features

- **Project Management**: Create, edit, and delete projects with associated categories, technologies, and media.
- **GitHub Integration**: Import project details directly from GitHub repositories.
- **Media Management**: Upload, manage, and associate media files with projects.
- **Category and Technology Management**: Organize projects by categories and technologies.
- **Custom Scraper Service**: Fetch project details from external sources.
- **Filament Admin Integration**: Use Filament for an intuitive admin panel.
- **Queue Jobs**: Handle media imports and other tasks asynchronously.

## Project Modules

### 1. **Controllers**
   - **`ProjectController`** ([app/Http/Controllers/ProjectController.php](app/Http/Controllers/ProjectController.php)): Handles project-related operations such as listing, creating, and importing projects. Includes methods for managing media and debugging.

### 2. **Models**
   - **`Project`** ([app/Models/Project.php](app/Models/Project.php)): Represents a project entity with relationships to categories, technologies, and media.
   - **`Category`** ([app/Models/Category.php](app/Models/Category.php)): Represents project categories.
   - **`Technology`** ([app/Models/Technology.php](app/Models/Technology.php)): Represents technologies associated with projects.
   - **`ProjectMedia`** ([app/Models/ProjectMedia.php](app/Models/ProjectMedia.php)): Handles media files linked to projects.

### 3. **Filament Resources**
   - **`ProjectResource`** ([app/Filament/Resources/ProjectResource.php](app/Filament/Resources/ProjectResource.php)): Provides Filament-based CRUD operations for projects.
   - **Pages**:
     - **`CreateProject`** ([app/Filament/Resources/ProjectResource/Pages/CreateProject.php](app/Filament/Resources/ProjectResource/Pages/CreateProject.php)): Handles project creation.
     - **`EditProject`** ([app/Filament/Resources/ProjectResource/Pages/EditProject.php](app/Filament/Resources/ProjectResource/Pages/EditProject.php)): Handles project editing.
     - **`ImportProject`** ([app/Filament/Resources/ProjectResource/Pages/ImportProject.php](app/Filament/Resources/ProjectResource/Pages/ImportProject.php)): Imports project data from GitHub.
     - **`ListProjects`** ([app/Filament/Resources/ProjectResource/Pages/ListProjects.php](app/Filament/Resources/ProjectResource/Pages/ListProjects.php)): Displays a list of projects.
- **`PostResource`** ([app/Filament/Resources/PostResource.php](app/Filament/Resources/PostResource.php)): Provides Filament-based CRUD operations for posts.  
  - **Pages**:  
    - **`CreatePost`** ([app/Filament/Resources/PostResource/Pages/CreatePost.php](app/Filament/Resources/PostResource/Pages/CreatePost.php)): Handles post creation.  
    - **`EditPost`** ([app/Filament/Resources/PostResource/Pages/EditPost.php](app/Filament/Resources/PostResource/Pages/EditPost.php)): Handles post editing.  
    - **`ListPosts`** ([app/Filament/Resources/PostResource/Pages/ListPosts.php](app/Filament/Resources/PostResource/Pages/ListPosts.php)): Displays a list of posts.

- **`CategoryResource`** ([app/Filament/Resources/CategoryResource.php](app/Filament/Resources/CategoryResource.php)): Provides Filament-based CRUD operations for categories.  
  - **Pages**:  
    - **`CreateCategory`** ([app/Filament/Resources/CategoryResource/Pages/CreateCategory.php](app/Filament/Resources/CategoryResource/Pages/CreateCategory.php)): Handles category creation.  
    - **`EditCategory`** ([app/Filament/Resources/CategoryResource/Pages/EditCategory.php](app/Filament/Resources/CategoryResource/Pages/EditCategory.php)): Handles category editing.  
    - **`ListCategories`** ([app/Filament/Resources/CategoryResource/Pages/ListCategories.php](app/Filament/Resources/CategoryResource/Pages/ListCategories.php)): Displays a list of categories.

- **`TechnologyResource`** ([app/Filament/Resources/TechnologyResource.php](app/Filament/Resources/TechnologyResource.php)): Provides Filament-based CRUD operations for technologies.  
  - **Pages**:  
    - **`CreateTechnology`** ([app/Filament/Resources/TechnologyResource/Pages/CreateTechnology.php](app/Filament/Resources/TechnologyResource/Pages/CreateTechnology.php)): Handles technology creation.  
    - **`EditTechnology`** ([app/Filament/Resources/TechnologyResource/Pages/EditTechnology.php](app/Filament/Resources/TechnologyResource/Pages/EditTechnology.php)): Handles technology editing.  
    - **`ListTechnologies`** ([app/Filament/Resources/TechnologyResource/Pages/ListTechnologies.php](app/Filament/Resources/TechnologyResource/Pages/ListTechnologies.php)): Displays a list of technologies.

- **`UserResource`** ([app/Filament/Resources/UserResource.php](app/Filament/Resources/UserResource.php)): Provides Filament-based CRUD operations for users.  
  - **Pages**:  
    - **`CreateUser`** ([app/Filament/Resources/UserResource/Pages/CreateUser.php](app/Filament/Resources/UserResource/Pages/CreateUser.php)): Handles user creation.  
    - **`EditUser`** ([app/Filament/Resources/UserResource/Pages/EditUser.php](app/Filament/Resources/UserResource/Pages/EditUser.php)): Handles user editing.  
    - **`ListUsers`** ([app/Filament/Resources/UserResource/Pages/ListUsers.php](app/Filament/Resources/UserResource/Pages/ListUsers.php)): Displays a list of users.

- **`ContactMessageResource`** ([app/Filament/Resources/ContactMessageResource.php](app/Filament/Resources/ContactMessageResource.php)): Provides Filament-based CRUD operations for contact messages.  
  - **Pages**:  
    - **`ListContactMessages`** ([app/Filament/Resources/ContactMessageResource/Pages/ListContactMessages.php](app/Filament/Resources/ContactMessageResource/Pages/ListContactMessages.php)): Displays a list of contact messages.


### 4. **Services**
   - **`ScraperService`** ([app/Services/ScraperService.php](app/Services/ScraperService.php)): Scrapes project data from GitHub or other sources.
   - **`HtmlSanitizerService`** ([app/Services/HtmlSanitizerService.php](app/Services/HtmlSanitizerService.php)): Sanitizes HTML content for project descriptions.

### 5. **Jobs**
   - **`ImportProjectMediaJob`** ([app/Jobs/ImportProjectMediaJob.php](app/Jobs/ImportProjectMediaJob.php)): Handles asynchronous media import for projects.

### 6. **Routes**
   - **Web Routes** ([routes/web.php](routes/web.php)): Defines routes for project management, including importing, creating, and listing projects.

### 7. **Views**
   - **Frontend Views**: Includes views for displaying projects, categories, and technologies.
   - **Filament Views**: Custom views for Filament admin pages.

## Installation

1. Clone the repository.
2. Install dependencies:
   ```bash
   composer install
   npm install
