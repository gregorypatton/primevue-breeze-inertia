<template>
  <div class="card">
    <Stepper :value="activeStep">
      <!-- Step 1: Upload Catalog File (XLSM) -->
      <StepItem value="1">
        <Step>Upload Catalog File</Step>
        <StepPanel v-slot="{ activateCallback }">
          <div class="flex flex-col h-48">
            <h3 class="font-medium">Step 1: Upload Catalog File (XLSM)</h3>
            <div class="border-2 border-dashed border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 flex-auto flex justify-center items-center">
              <FileUpload
                mode="basic"
                name="catalogFile"
                accept=".xlsm"
                :customUpload="true"
                @select="handleCatalogFile"
                chooseLabel="Choose Catalog File"
              />
              <span v-if="catalogFileName" class="ml-4">{{ catalogFileName }}</span>
            </div>
          </div>
          <div class="py-6">
            <Button label="Next" @click="nextStep(activateCallback, '2')" :disabled="!catalogFileSelected" />
          </div>
        </StepPanel>
      </StepItem>

      <!-- Step 2: Upload FBA File (TXT) -->
      <StepItem value="2">
        <Step>Upload FBA File</Step>
        <StepPanel v-slot="{ activateCallback }">
          <div class="flex flex-col h-48">
            <h3 class="font-medium">Step 2: Upload FBA File (TXT)</h3>
            <div class="border-2 border-dashed border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 flex-auto flex justify-center items-center">
              <FileUpload
                mode="basic"
                name="fbaFile"
                accept=".txt"
                :customUpload="true"
                @select="handleFbaFile"
                chooseLabel="Choose FBA File"
              />
              <span v-if="fbaFileName" class="ml-4">{{ fbaFileName }}</span>
            </div>
          </div>
          <div class="flex py-6 gap-2">
            <Button label="Back" severity="secondary" @click="activateCallback('1')" />
            <Button label="Next" @click="nextStep(activateCallback, '3')" :disabled="!fbaFileSelected" />
          </div>
        </StepPanel>
      </StepItem>

      <!-- Step 3: Upload Files -->
      <StepItem value="3">
        <Step>Summary & Upload</Step>
        <StepPanel v-slot="{ activateCallback }">
          <div class="flex flex-col h-48">
            <h3 class="font-medium">Step 3: Summary</h3>
            <div class="border-2 border-dashed border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 flex-auto flex justify-center items-center">
              <p>Catalog File: {{ catalogFileName }}</p>
              <p>FBA File: {{ fbaFileName }}</p>
            </div>
          </div>
          <div class="py-6">
            <Button label="Back" severity="secondary" @click="activateCallback('2')" />
            <Button label="Upload" @click="uploadFiles" />
          </div>
        </StepPanel>
      </StepItem>
    </Stepper>
  </div>
</template>

<script>
import Stepper from 'primevue/stepper';
import StepItem from 'primevue/stepitem';
import StepPanel from 'primevue/steppanel';
import FileUpload from 'primevue/fileupload';
import Button from 'primevue/button';

export default {
  components: {
    Stepper,
    StepItem,
    StepPanel,
    FileUpload,
    Button,
  },
  data() {
    return {
      activeStep: '1', // Current active step
      catalogFileName: '', // Catalog file name
      fbaFileName: '', // FBA file name
      catalogFile: null, // Actual file object
      fbaFile: null, // Actual file object
      catalogFileSelected: false, // Check if catalog file is selected
      fbaFileSelected: false, // Check if FBA file is selected
    };
  },
  methods: {
    handleCatalogFile(event) {
      this.catalogFile = event.files[0]; // Capture catalog file
      this.catalogFileName = this.catalogFile.name;
      this.catalogFileSelected = true;
    },
    handleFbaFile(event) {
      this.fbaFile = event.files[0]; // Capture FBA file
      this.fbaFileName = this.fbaFile.name;
      this.fbaFileSelected = true;
    },
    nextStep(activateCallback, nextStepValue) {
      activateCallback(nextStepValue);
      this.activeStep = nextStepValue;
    },
    async uploadFiles() {
      const formData = new FormData();
      formData.append('catalogFile', this.catalogFile);
      formData.append('fbaFile', this.fbaFile);
      formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content')); // Include CSRF token

      // Make the request to upload both files
      await this.$inertia.post('/catalogs/upload', formData, {
        onSuccess: () => {
          console.log('Files uploaded successfully!');
        },
        onError: (errors) => {
          console.error(errors);
        },
      });
    },
  },
};
</script>

<style scoped>

</style>
