<template>
  <div v-if="open" class="dialog-overlay">
    <div class="dialog">
      <h2>Send Email to Customer</h2>
      <form @submit.prevent="sendEmail">
        <div>
          <label>Subject:</label>
          <input v-model="form.subject" required />
        </div>
        <div>
          <label>To:</label>
          <input
            v-model="form.toInput"
            @keyup.enter="addToEmail"
            placeholder="Add email and press Enter"
          />
          <div>
            <span v-for="(email, idx) in form.to" :key="idx" class="email-chip">
              {{ email }}
              <button type="button" @click="removeToEmail(idx)">x</button>
            </span>
          </div>
        </div>
        <div>
          <label>CC:</label>
          <input
            v-model="form.ccInput"
            @keyup.enter="addCcEmail"
            placeholder="Add CC email and press Enter"
          />
          <div>
            <span v-for="(email, idx) in form.cc" :key="idx" class="email-chip">
              {{ email }}
              <button type="button" @click="removeCcEmail(idx)">x</button>
            </span>
          </div>
        </div>
        <div>
          <label>Body:</label>
          <textarea v-model="form.body" required rows="6"></textarea>
        </div>
        <div class="dialog-actions">
          <button type="submit">Send</button>
          <button type="button" @click="$emit('close')">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, defineEmits, defineProps } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  open: Boolean,
  customerEmail: String,
  customerId: Number,
});
const emit = defineEmits(['close', 'sent']);

const form = ref({
  subject: '',
  to: [props.customerEmail],
  toInput: '',
  cc: [],
  ccInput: '',
  body: '',
});

watch(() => props.customerEmail, (newEmail) => {
  form.value.to = [newEmail];
});

function addToEmail() {
  if (form.value.toInput && !form.value.to.includes(form.value.toInput)) {
    form.value.to.push(form.value.toInput);
    form.value.toInput = '';
  }
}
function removeToEmail(idx: number) {
  form.value.to.splice(idx, 1);
}

function addCcEmail() {
  if (form.value.ccInput && !form.value.cc.includes(form.value.ccInput)) {
    form.value.cc.push(form.value.ccInput);
    form.value.ccInput = '';
  }
}
function removeCcEmail(idx: number) {
  form.value.cc.splice(idx, 1);
}

function sendEmail() {
  router.post(
    '/emails/send',
    {
      subject: form.value.subject,
      to: form.value.to,
      cc: form.value.cc,
      body: form.value.body,
      customer_id: props.customerId,
    },
    {
      onSuccess: () => {
        alert('Email sent!');
        emit('sent');
        emit('close');
      },
    }
  );
}
</script>

<style scoped>
.dialog-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.3);
  display: flex; align-items: center; justify-content: center;
  z-index: 1000;
}
.dialog {
  background: #fff;
  padding: 2rem;
  border-radius: 8px;
  min-width: 400px;
  box-shadow: 0 2px 16px rgba(0,0,0,0.2);
}
.email-chip {
  display: inline-block;
  background: #eee;
  margin: 2px;
  padding: 2px 8px;
  border-radius: 12px;
}
.dialog-actions {
  margin-top: 1rem;
  display: flex;
  gap: 1rem;
}
</style>
