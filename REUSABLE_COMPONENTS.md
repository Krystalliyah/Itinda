# Reusable UI Components Documentation

This document provides a comprehensive overview of all reusable UI components available in the iTinda application.

## Table of Contents
- [Modal Components](#modal-components)
- [Table Components](#table-components)
- [Form Components](#form-components)
- [UI Components](#ui-components)
- [Navigation Components](#navigation-components)

---

## Modal Components

### 1. ConfirmationModal
**Location:** `resources/js/components/ui/modal/ConfirmationModal.vue`

A simple, reusable confirmation dialog for destructive or important actions like delete, logout, or any action requiring user confirmation.

**Features:**
- Configurable title and description
- Customizable button text
- Support for destructive variant (red button)
- Loading state support
- Consistent styling with app design system

**Props:**
```typescript
{
  open: boolean;              // Controls modal visibility
  title: string;              // Modal title
  description: string;        // Modal description text
  confirmText?: string;       // Confirm button text (default: "Confirm")
  cancelText?: string;        // Cancel button text (default: "Cancel")
  variant?: 'default' | 'destructive'; // Button style (default: "default")
  loading?: boolean;          // Disable buttons during loading (default: false)
}
```

**Events:**
- `@confirm` - Emitted when confirm button is clicked
- `@cancel` - Emitted when cancel button is clicked
- `@update:open` - Emitted when modal visibility changes

**Usage Example:**
```vue
<script setup lang="ts">
import { ref } from 'vue';
import { ConfirmationModal } from '@/components/ui/modal';

const showDeleteModal = ref(false);

const handleDelete = () => {
    // Perform delete action
    showDeleteModal.value = false;
};
</script>

<template>
    <Button @click="showDeleteModal = true">Delete Item</Button>

    <ConfirmationModal
        v-model:open="showDeleteModal"
        title="Delete Item"
        description="Are you sure you want to delete this item? This action cannot be undone."
        confirm-text="Delete"
        variant="destructive"
        @confirm="handleDelete"
    />
</template>
```

**Use Cases:**
- Delete confirmations
- Logout confirmations
- Irreversible action confirmations
- Account deactivation
- Data removal warnings

---

### 2. FormModal
**Location:** `resources/js/components/ui/modal/FormModal.vue`

A flexible modal component designed for forms with submit and cancel actions. Perfect for create/edit operations.

**Features:**
- Flexible content slot for any form fields
- Configurable modal width
- Submit/cancel handlers
- Loading state support
- Consistent footer layout

**Props:**
```typescript
{
  open: boolean;              // Controls modal visibility
  title: string;              // Modal title
  description?: string;       // Optional modal description
  submitText?: string;        // Submit button text (default: "Submit")
  cancelText?: string;        // Cancel button text (default: "Cancel")
  loading?: boolean;          // Disable buttons during loading (default: false)
  maxWidth?: 'sm' | 'md' | 'lg' | 'xl' | '2xl' | '3xl'; // Modal width (default: "md")
}
```

**Events:**
- `@submit` - Emitted when submit button is clicked
- `@cancel` - Emitted when cancel button is clicked
- `@update:open` - Emitted when modal visibility changes

**Usage Example:**
```vue
<script setup lang="ts">
import { ref } from 'vue';
import { FormModal } from '@/components/ui/modal';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const showFormModal = ref(false);
const formData = ref({ name: '', email: '' });

const handleSubmit = () => {
    // Submit form data
    console.log('Form submitted:', formData.value);
    showFormModal.value = false;
};
</script>

<template>
    <Button @click="showFormModal = true">Add User</Button>

    <FormModal
        v-model:open="showFormModal"
        title="Add New User"
        description="Enter the user details below."
        submit-text="Create User"
        max-width="lg"
        @submit="handleSubmit"
    >
        <div class="grid gap-4">
            <div class="grid gap-2">
                <Label for="name">Name</Label>
                <Input id="name" v-model="formData.name" placeholder="John Doe" />
            </div>
            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input id="email" type="email" v-model="formData.email" />
            </div>
        </div>
    </FormModal>
</template>
```

**Use Cases:**
- Add/Create forms
- Edit forms
- Multi-field input dialogs
- Settings forms
- Profile updates

---

## Table Components

### Table Component System
**Location:** `resources/js/components/ui/table/`

A complete table component system with consistent styling and behavior.

**Components:**
1. **Table** - Main wrapper with overflow handling
2. **TableHeader** - Header section container
3. **TableBody** - Body section container
4. **TableRow** - Individual row with hover effects
5. **TableHead** - Header cell
6. **TableCell** - Data cell
7. **TableCaption** - Optional table caption

**Features:**
- Responsive design with overflow scrolling
- Hover effects on rows
- Consistent spacing and typography
- Support for checkboxes and actions
- Accessible markup

**Usage Example:**
```vue
<script setup lang="ts">
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Button } from '@/components/ui/button';

const products = [
    { id: 1, name: 'Product A', price: 29.99, stock: 100 },
    { id: 2, name: 'Product B', price: 49.99, stock: 50 },
    { id: 3, name: 'Product C', price: 19.99, stock: 200 },
];
</script>

<template>
    <Table>
        <TableCaption>A list of your products.</TableCaption>
        <TableHeader>
            <TableRow>
                <TableHead>Product Name</TableHead>
                <TableHead>Price</TableHead>
                <TableHead>Stock</TableHead>
                <TableHead class="text-right">Actions</TableHead>
            </TableRow>
        </TableHeader>
        <TableBody>
            <TableRow v-for="product in products" :key="product.id">
                <TableCell class="font-medium">{{ product.name }}</TableCell>
                <TableCell>${{ product.price }}</TableCell>
                <TableCell>{{ product.stock }}</TableCell>
                <TableCell class="text-right">
                    <Button variant="ghost" size="sm">Edit</Button>
                    <Button variant="ghost" size="sm">Delete</Button>
                </TableCell>
            </TableRow>
        </TableBody>
    </Table>
</template>
```

**Use Cases:**
- Product listings
- User management tables
- Order lists
- Inventory displays
- Data grids
- Admin dashboards

---

## Form Components

### 1. Input
**Location:** `resources/js/components/ui/input/Input.vue`

Standard text input with validation states and consistent styling.

**Features:**
- Support for all input types
- Validation state styling (aria-invalid)
- Focus ring effects
- Disabled state
- File input styling

**Usage:**
```vue
<Input 
    id="email" 
    type="email" 
    v-model="email"
    :aria-invalid="!!errors.email"
    placeholder="email@example.com" 
/>
```

---

### 2. InputError
**Location:** `resources/js/components/InputError.vue`

Displays validation error messages in red text.

**Props:**
```typescript
{
  message?: string; // Error message to display
}
```

**Usage:**
```vue
<InputError :message="errors.email" />
```

---

### 3. Label
**Location:** `resources/js/components/ui/label/Label.vue`

Form label component with consistent styling.

**Usage:**
```vue
<Label for="email">Email address</Label>
<Input id="email" type="email" />
```

---

### 4. Checkbox
**Location:** `resources/js/components/ui/checkbox/Checkbox.vue`

Checkbox input with custom styling.

**Usage:**
```vue
<Checkbox id="terms" v-model="acceptedTerms" />
<Label for="terms">Accept terms and conditions</Label>
```

---

## UI Components

### 1. Button
**Location:** `resources/js/components/ui/button/Button.vue`

Versatile button component with multiple variants and sizes.

**Variants:**
- `default` - Primary button
- `destructive` - Red button for dangerous actions
- `outline` - Outlined button
- `secondary` - Secondary style
- `ghost` - Transparent button
- `link` - Link-styled button

**Sizes:**
- `default` - Standard size
- `sm` - Small
- `lg` - Large
- `icon` - Icon-only button

**Usage:**
```vue
<Button variant="default">Save</Button>
<Button variant="destructive">Delete</Button>
<Button variant="outline">Cancel</Button>
<Button variant="ghost" size="sm">Edit</Button>
```

---

### 2. Card
**Location:** `resources/js/components/ui/card/`

Card container components for content grouping.

**Components:**
- `Card` - Main container
- `CardHeader` - Header section
- `CardTitle` - Title text
- `CardDescription` - Description text
- `CardContent` - Main content area
- `CardFooter` - Footer section

**Usage:**
```vue
<Card>
    <CardHeader>
        <CardTitle>Card Title</CardTitle>
        <CardDescription>Card description text</CardDescription>
    </CardHeader>
    <CardContent>
        <p>Card content goes here</p>
    </CardContent>
    <CardFooter>
        <Button>Action</Button>
    </CardFooter>
</Card>
```

---

### 3. Badge
**Location:** `resources/js/components/ui/badge/Badge.vue`

Small status indicators or labels.

**Variants:**
- `default` - Standard badge
- `secondary` - Secondary style
- `destructive` - Red badge
- `outline` - Outlined badge

**Usage:**
```vue
<Badge>New</Badge>
<Badge variant="destructive">Urgent</Badge>
<Badge variant="outline">Draft</Badge>
```

---

### 4. Alert
**Location:** `resources/js/components/ui/alert/`

Alert/notification components for displaying messages.

**Components:**
- `Alert` - Main container
- `AlertTitle` - Alert title
- `AlertDescription` - Alert description

**Usage:**
```vue
<Alert>
    <AlertTitle>Success</AlertTitle>
    <AlertDescription>Your changes have been saved.</AlertDescription>
</Alert>
```

---

### 5. Dialog
**Location:** `resources/js/components/ui/dialog/`

Base dialog/modal components (used by ConfirmationModal and FormModal).

**Components:**
- `Dialog` - Main dialog wrapper
- `DialogContent` - Content container
- `DialogHeader` - Header section
- `DialogTitle` - Dialog title
- `DialogDescription` - Dialog description
- `DialogFooter` - Footer section
- `DialogScrollContent` - Scrollable content variant

---

### 6. Dropdown Menu
**Location:** `resources/js/components/ui/dropdown-menu/`

Dropdown menu components for actions and navigation.

**Components:**
- `DropdownMenu` - Main wrapper
- `DropdownMenuTrigger` - Trigger button
- `DropdownMenuContent` - Menu content
- `DropdownMenuItem` - Menu item
- `DropdownMenuLabel` - Menu label
- `DropdownMenuSeparator` - Separator line
- `DropdownMenuGroup` - Item grouping

**Usage:**
```vue
<DropdownMenu>
    <DropdownMenuTrigger>
        <Button>Options</Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent>
        <DropdownMenuItem>Edit</DropdownMenuItem>
        <DropdownMenuItem>Delete</DropdownMenuItem>
    </DropdownMenuContent>
</DropdownMenu>
```

---

### 7. Tooltip
**Location:** `resources/js/components/ui/tooltip/`

Tooltip components for additional information on hover.

**Usage:**
```vue
<Tooltip>
    <TooltipTrigger>Hover me</TooltipTrigger>
    <TooltipContent>
        <p>Tooltip content</p>
    </TooltipContent>
</Tooltip>
```

---

### 8. Spinner
**Location:** `resources/js/components/ui/spinner/Spinner.vue`

Loading spinner component.

**Usage:**
```vue
<Spinner />
```

---

### 9. Skeleton
**Location:** `resources/js/components/ui/skeleton/Skeleton.vue`

Loading skeleton for content placeholders.

**Usage:**
```vue
<Skeleton class="h-12 w-full" />
```

---

## Navigation Components

### 1. Sidebar
**Location:** `resources/js/components/Sidebar.vue`

Main sidebar navigation with collapsible functionality.

**Features:**
- Collapsible design
- Role-based navigation
- Logout confirmation modal
- Icon tooltips when collapsed

---

### 2. AppHeader
**Location:** `resources/js/components/AppHeader.vue`

Application header with user menu and navigation.

---

### 3. UserMenuContent
**Location:** `resources/js/components/UserMenuContent.vue`

User dropdown menu with settings and logout.

**Features:**
- User info display
- Settings link
- Logout with confirmation modal

---

### 4. VendorNav
**Location:** `resources/js/components/navigation/VendorNav.vue`

Vendor-specific navigation component.

---

## Best Practices

### 1. Component Composition
Always compose components together for consistent UI:
```vue
<Card>
    <CardHeader>
        <CardTitle>User Form</CardTitle>
    </CardHeader>
    <CardContent>
        <div class="grid gap-4">
            <div class="grid gap-2">
                <Label for="name">Name</Label>
                <Input id="name" v-model="name" />
                <InputError :message="errors.name" />
            </div>
        </div>
    </CardContent>
</Card>
```

### 2. Validation States
Always bind validation states to inputs:
```vue
<Input 
    :aria-invalid="!!errors.email"
    v-model="email"
/>
<InputError :message="errors.email" />
```

### 3. Modal Usage
Use appropriate modal for the use case:
- **ConfirmationModal** - For yes/no confirmations
- **FormModal** - For forms with multiple fields
- **Dialog** - For custom modal content

### 4. Consistent Spacing
Use Tailwind spacing utilities consistently:
- `gap-2` for tight spacing
- `gap-4` for standard spacing
- `gap-6` for loose spacing

### 5. Responsive Design
Always consider mobile responsiveness:
```vue
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <!-- Content -->
</div>
```

---

## Component Import Patterns

### Individual Imports
```typescript
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
```

### Group Imports
```typescript
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
```

### Modal Imports
```typescript
import { ConfirmationModal, FormModal } from '@/components/ui/modal';
```

---

## Styling Guidelines

### Color Palette
- **Primary**: Green (#1B4D3E)
- **Accent**: Gold (#C5A059)
- **Destructive**: Red (#DC2626)
- **Muted**: Gray shades

### Typography
- **Font Family**: Inter, Lato, Montserrat
- **Headings**: Bold, larger sizes
- **Body**: Regular weight, readable sizes

### Spacing
- Use Tailwind spacing scale (0.25rem increments)
- Consistent padding/margin across components

---

## Additional Resources

- **Tailwind CSS Documentation**: https://tailwindcss.com/docs
- **Reka UI (Radix Vue)**: https://reka-ui.com
- **Vue 3 Documentation**: https://vuejs.org
- **Inertia.js Documentation**: https://inertiajs.com

---

## Contributing

When creating new reusable components:

1. Follow existing component patterns
2. Use TypeScript for props and events
3. Include proper accessibility attributes
4. Add to this documentation
5. Create usage examples
6. Test across different screen sizes
7. Ensure dark mode compatibility

---

**Last Updated:** March 4, 2026
