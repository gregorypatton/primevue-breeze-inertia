<template>
  <div>
    <h1>Amazon Files Upload</h1>
    <Steps :model="steps" :activeIndex="activeStep" />

    <!-- Step 1: Upload Category+Listings+Report.xlsm -->
    <div v-if="activeStep === 0">
      <h2>Step 1: Upload Category+Listings+Report.xlsm</h2>
      <input
        type="file"
        @change="handleCategoryFileSelection"
        accept=".xlsm"
      />
      <p v-if="categoryFileName">Selected file: {{ categoryFileName }}</p>
      <Button label="Next" :disabled="!categoryFileValidated" @click="goToStep(1)" />
    </div>

    <!-- Step 2: Upload Amazon-fulfilled+Inventory.txt -->
    <div v-if="activeStep === 1">
      <h2>Step 2: Upload Amazon-fulfilled+Inventory.txt</h2>
      <input
        type="file"
        @change="handleFbaFileSelection"
        accept=".txt"
      />
      <p v-if="fnskuFileName">Selected file: {{ fnskuFileName }}</p>
      <Button label="Next" :disabled="!fnskuFileValidated" @click="goToStep(2)" />
    </div>

    <!-- Step 3: Confirm and Upload -->
    <div v-if="activeStep === 2">
      <h2>Step 3: Confirm and Upload</h2>
      <div>
        <p>Category+Listings+Report.xlsm: {{ categoryFileName }}</p>
        <p>Amazon-fulfilled+Inventory.txt: {{ fnskuFileName }}</p>
      </div>
      <Button label="Upload" :disabled="!canUpload" @click="submitForm" />
    </div>
  </div>
</template>

<script>
import { useForm } from '@inertiajs/vue3';
import Steps from 'primevue/steps';
import Button from 'primevue/button';

export default {
  name: "UploadAmazonFiles",
  components: {
    Steps,
    Button,
  },
  data() {
    return {
      activeStep: 0,
      steps: [
        { label: "Upload Category+Listings+Report.xlsm" },
        { label: "Upload Amazon-fulfilled+Inventory.txt" },
        { label: "Confirm and Upload" },
      ],
      categoryFileName: null,
      fnskuFileName: null,
      categoryFileValidated: false,
      fnskuFileValidated: false,
      categoryFile: null,  // Holds actual file object for upload
      fnskuFile: null,       // Holds actual file object for upload
    };
  },
  computed: {
    canUpload() {
      return this.categoryFileValidated && this.fnskuFileValidated;
    },
  },
  methods: {
    goToStep(step) {
      if (step >= 0 && step < this.steps.length) {
        this.activeStep = step;
      }
    },

    // Regex to match either file type with date pattern
    validateFileName(fileName, fileType) {
      const expectedFileRegex = /(Category\+Listings\+Report|Amazon-fulfilled\+Inventory)\+\d{2}-\d{2}-\d{4}\.(xlsm|txt)/;
      return expectedFileRegex.test(fileName) && fileName.endsWith(fileType);
    },

    // Handle Category File Selection
    handleCategoryFileSelection(event) {
      const file = event.target.files[0];

      if (file && this.validateFileName(file.name, '.xlsm')) {
        this.categoryFileName = file.name;
        this.categoryFileValidated = true;
        this.categoryFile = file;  // Store the actual file for upload
        this.goToStep(1);  // Advance to next step after validation
      } else {
        this.$toast.add({
          severity: 'error',
          summary: 'Invalid File',
          detail: 'The file does not match the required pattern (Category+Listings+Report+MM-DD-YYYY.xlsm).'
        });
        this.categoryFileValidated = false;
        this.categoryFile = null;
      }
    },

    // Handle FBA File Selection
    handleFbaFileSelection(event) {
      const file = event.target.files[0];

      if (file && this.validateFileName(file.name, '.txt')) {
        this.fnskuFileName = file.name;
        this.fnskuFileValidated = true;
        this.fnskuFile = file;  // Store the actual file for upload
        this.goToStep(2);  // Advance to next step after validation
      } else {
        this.$toast.add({
          severity: 'error',
          summary: 'Invalid File',
          detail: 'The file does not match the required pattern (Amazon-fulfilled+Inventory+MM-DD-YYYY.txt).'
        });
        this.fnskuFileValidated = false;
        this.fnskuFile = null;
      }
    },

    // Submit the form
    submitForm() {
      const form = useForm({
        categoryFile: this.categoryFile,
        fnskuFile: this.fnskuFile,
      });

      const formData = new FormData();
      formData.append('categoryFile', this.categoryFile);
      formData.append('fnskuFile', this.fnskuFile);

        form.post(route('catalog.upload'), {
        forceFormData: true,
        data: formData,
        onSuccess: () => {
          this.$toast.add({
            severity: 'success',
            summary: 'Success',
            detail: 'Files uploaded successfully!'
          });
        },
        onError: (errors) => {
          this.$toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'There was an issue uploading the files.'
          });
        },
      });
    },
  },
};
</script>

<style scoped>
h2 {
  margin-top: 20px;
}
</style>
