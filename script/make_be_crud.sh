#!/bin/bash

# === Disable script exit on error ===
set +e  # Disable script exit on error

# ========== MENU CHOOSING ACTIONS ==========
echo "📁 Enter Folder name for Controller/Request/Resource/Service/Repository/Test (Press Enter to use Model name as folder):"
read FOLDER_NAME

echo "📝 Enter Model name (e.g. Example):"
read MODEL_NAME

if [ -z "$MODEL_NAME" ]; then
  echo "❌ Model name cannot be empty."
  exit 1
fi

# If no FOLDER_NAME, skip using a subfolder
if [ -z "$FOLDER_NAME" ]; then
  FOLDER_NAME=""
fi

MODEL_LOWER=$(echo "$MODEL_NAME" | awk '{print tolower($0)}')

# ========== MENU CHOOSING ACTIONS ==========
echo ""
echo "🧩 What do you want to create for model '$MODEL_NAME' in folder '$FOLDER_NAME'? (Enter numbers separated by space)"
echo "  1) Model + Migration"
echo "  2) API Controller"
echo "  3) Form Request"
echo "  4) API Resource"
echo "  5) Service"
echo "  6) Route (add to routes/api.php)"
echo "  7) Repository + Interface"
echo "  8) Unit Tests (Service, Request, Repository)"
echo "  9) Integration Test"
echo ""

read -p "Your choice: " choices

for choice in $choices; do
  case $choice in
    1)
      echo "📦 Creating Model and Migration..."
      # Model doesn't need FOLDER_NAME
      php artisan make:model "${MODEL_NAME}" --migration || echo "⚠️ Error occurred while creating model and migration."
      ;;
    2)
      echo "🧭 Creating API Controller..."
      if [ -z "$FOLDER_NAME" ]; then
        php artisan make:controller "${MODEL_NAME}Controller" --api || echo "⚠️ Error occurred while creating API controller."
      else
        php artisan make:controller "${FOLDER_NAME}/${MODEL_NAME}Controller" --api || echo "⚠️ Error occurred while creating API controller."
      fi
      ;;
    3)
      echo "📝 Creating Form Requests (Store + Update)..."
      if [ -z "$FOLDER_NAME" ]; then
        php artisan make:request "${MODEL_NAME}StoreRequest" || echo "⚠️ Error occurred while creating store request."
        php artisan make:request "${MODEL_NAME}UpdateRequest" || echo "⚠️ Error occurred while creating update request."
      else
        php artisan make:request "${FOLDER_NAME}/${MODEL_NAME}StoreRequest" || echo "⚠️ Error occurred while creating store request."
        php artisan make:request "${FOLDER_NAME}/${MODEL_NAME}UpdateRequest" || echo "⚠️ Error occurred while creating update request."
      fi
      ;;
    4)
      echo "📤 Creating API Resource..."
      if [ -z "$FOLDER_NAME" ]; then
        php artisan make:resource "${MODEL_NAME}Resource" || echo "⚠️ Error occurred while creating API resource."
      else
        php artisan make:resource "${FOLDER_NAME}/${MODEL_NAME}Resource" || echo "⚠️ Error occurred while creating API resource."
      fi
      ;;
    5)
        echo "🔧 Creating Service..."
        # Create service in app/Http/Services/{FOLDER_NAME}/{MODEL_NAME}Service.php
        if [ -z "$FOLDER_NAME" ]; then
            SERVICE_PATH="app/Http/Services/${MODEL_NAME}Service.php"
        else
            SERVICE_PATH="app/Http/Services/${FOLDER_NAME}/${MODEL_NAME}Service.php"
        fi

    # Create Service file
    echo "<?php

namespace App\Http\Services${FOLDER_NAME:+\\${FOLDER_NAME}};

use App\Http\Services\BaseService;
use App\Repositories\Contracts${FOLDER_NAME:+\\${FOLDER_NAME}}\\${MODEL_NAME}RepositoryInterface;

class ${MODEL_NAME}Service extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(${MODEL_NAME}RepositoryInterface \$repository)
    {
        parent::__construct(\$repository);
    }
}" > "$SERVICE_PATH"

  echo "✅ Service created at $SERVICE_PATH" || echo "⚠️ Error occurred while creating service."
  ;;
    6)
      echo "➕ Adding route to routes/api.php if it doesn't exist..."

      # Create route file in routes/modules/{model_name}.php
      MODULE_NAME_LOWER=$(echo "$MODEL_NAME" | awk '{print tolower($0)}')
      MODULE_ROUTE_PATH="routes/modules/${MODULE_NAME_LOWER}.php"

      # Check if route file exists, if not create a new one
      if [ ! -f "$MODULE_ROUTE_PATH" ]; then
        echo "Creating route file at $MODULE_ROUTE_PATH"
        echo "<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\\${FOLDER_NAME:+${FOLDER_NAME}\\}${MODEL_NAME}Controller;

Route::prefix('${MODULE_NAME_LOWER}')->middleware('auth:api')->group(function () {
    Route::apiResource('', ${MODEL_NAME}Controller::class)->parameters(['' => '${MODULE_NAME_LOWER}']);
});" > $MODULE_ROUTE_PATH
      fi

      # Add route to api.php if it doesn't exist
      ROUTE_FILE="routes/api.php"
      if ! grep -q "require __DIR__ . '/modules/${MODULE_NAME_LOWER}.php';" "$ROUTE_FILE"; then
        echo "// Import ${MODULE_NAME_LOWER} route" >> $ROUTE_FILE
        echo "require __DIR__ . '/modules/${MODULE_NAME_LOWER}.php';" >> $ROUTE_FILE
        echo "✅ Route added to $ROUTE_FILE" || echo "⚠️ Error occurred while adding route to api.php"
      else
        echo "⚠️ Route already exists in $ROUTE_FILE"
      fi
      ;;
      7)
        echo "🔍 Creating Repository..."

        # Create folder for FOLDER_NAME if it doesn't exist
        if [ -n "$FOLDER_NAME" ]; then
            mkdir -p "app/Repositories/Eloquent/${FOLDER_NAME}"
        fi

        # Set paths for the Repository file and Interface
        REPOSITORY_PATH="app/Repositories/Eloquent/${FOLDER_NAME:+$FOLDER_NAME/}${MODEL_NAME}Repository.php"
        REPOSITORY_INTERFACE_PATH="app/Repositories/Contracts/${FOLDER_NAME:+$FOLDER_NAME/}${MODEL_NAME}RepositoryInterface.php"

        # Create the interface file
        echo "<?php

namespace App\Repositories\Contracts${FOLDER_NAME:+\\$FOLDER_NAME};

interface ${MODEL_NAME}RepositoryInterface
{
    public function all(\$conditions = []);
    public function find(\$id);
    public function create(array \$data);
    public function update(array \$data, \$id);
    public function delete(\$id);
}" > $REPOSITORY_INTERFACE_PATH

    # Create the repository implementation file with the required structure
    echo "<?php

namespace App\Repositories\Eloquent${FOLDER_NAME:+\\$FOLDER_NAME};

use App\Models\\${MODEL_NAME};
use App\Repositories\Contracts${FOLDER_NAME:+\\$FOLDER_NAME}\\${MODEL_NAME}RepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class ${MODEL_NAME}Repository extends BaseRepository implements ${MODEL_NAME}RepositoryInterface
{
    /**
    * Create a new class instance.
    */
    public function __construct(${MODEL_NAME} \$model)
    {
        parent::__construct(\$model);
    }
}" > $REPOSITORY_PATH

        echo "✅ Repository created at $REPOSITORY_PATH and $REPOSITORY_INTERFACE_PATH" || echo "⚠️ Error occurred while creating repository."
        ;;
    8)
        echo "🧪 Creating Unit Tests for Repository, Service, and Request..."

        # Create directories for Unit Test if not exist
        if [ -n "$FOLDER_NAME" ]; then
            mkdir -p "tests/Unit/${FOLDER_NAME}/${MODEL_NAME}"
        fi

        # Create file for Repository Test
        REPOSITORY_TEST_PATH="tests/Unit/${FOLDER_NAME:+$FOLDER_NAME/}${MODEL_NAME}/${MODEL_NAME}RepositoryTest.php"
        if [ ! -f "$REPOSITORY_TEST_PATH" ]; then
            php artisan make:test "${FOLDER_NAME:+$FOLDER_NAME/}${MODEL_NAME}/${MODEL_NAME}RepositoryTest" --unit
            echo "✅ Repository Test created at $REPOSITORY_TEST_PATH"
        else
            echo "⚠️ Repository Test file already exists at $REPOSITORY_TEST_PATH"
        fi

        # Create file for Service Test
        SERVICE_TEST_PATH="tests/Unit/${FOLDER_NAME:+$FOLDER_NAME/}${MODEL_NAME}/${MODEL_NAME}ServiceTest.php"
        if [ ! -f "$SERVICE_TEST_PATH" ]; then
            php artisan make:test "${FOLDER_NAME:+$FOLDER_NAME/}${MODEL_NAME}/${MODEL_NAME}ServiceTest" --unit
            echo "✅ Service Test created at $SERVICE_TEST_PATH"
        else
            echo "⚠️ Service Test file already exists at $SERVICE_TEST_PATH"
        fi

        # Create file for Request Test
        REQUEST_TEST_PATH="tests/Unit/${FOLDER_NAME:+$FOLDER_NAME/}${MODEL_NAME}/${MODEL_NAME}RequestTest.php"
        if [ ! -f "$REQUEST_TEST_PATH" ]; then
            php artisan make:test "${FOLDER_NAME:+$FOLDER_NAME/}${MODEL_NAME}/${MODEL_NAME}RequestTest" --unit
            echo "✅ Request Test created at $REQUEST_TEST_PATH"
        else
            echo "⚠️ Request Test file already exists at $REQUEST_TEST_PATH"
        fi
        ;;
    9)
        echo "🧪 Creating Integration Test..."

        # Path of Integration Test file
        INTEGRATION_TEST_PATH="tests/Feature/${FOLDER_NAME}/${MODEL_NAME}IntegrationTest.php"

        if [ ! -f "$INTEGRATION_TEST_PATH" ]; then
            # Create Integration Test using artisan (mặc định tạo vào tests/Feature)
            php artisan make:test "${FOLDER_NAME}/${MODEL_NAME}IntegrationTest"

            echo "✅ Integration Test created at $INTEGRATION_TEST_PATH"
        else
            echo "⚠️ Integration Test already exists at $INTEGRATION_TEST_PATH"
        fi
        ;;
    *)
      echo "❌ Invalid choice: $choice"
      ;;
  esac
done

# === Re-enable exit on error ===
set -e  # Re-enable script exit on error

echo ""
echo "✅ Completed the selected tasks for '$MODEL_NAME' in folder '$FOLDER_NAME'!"
