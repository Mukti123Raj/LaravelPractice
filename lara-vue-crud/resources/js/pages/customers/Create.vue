<template>
  <AppLayout :title="isEditing ? 'Edit Customer' : 'Create Customer'">
    <template #header>
      <div class="flex items-center justify-between">
        <Heading>{{ isEditing ? 'Edit Customer' : 'Create Customer' }}</Heading>
        <Button variant="outline" as-child>
          <Link :href="route('customers.index')">
            <Icon name="arrow-left" class="mr-2 h-4 w-4" />
            Back to Customers
          </Link>
        </Button>
      </div>
    </template>

    <div class="max-w-2xl mx-auto">
      <Card>
        <CardHeader>
          <CardTitle>{{ isEditing ? 'Edit Customer Information' : 'Add New Customer' }}</CardTitle>
          <CardDescription>
            {{ isEditing ? 'Update the customer information below.' : 'Fill in the customer details below.' }}
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="submitForm" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Name -->
              <div class="space-y-2">
                <Label for="name">Customer Name *</Label>
                <Input
                  id="name"
                  v-model="form.name"
                  type="text"
                  placeholder="Enter customer name"
                  :className="{ 'border-red-500': errors.name }"
                />
                <InputError v-if="errors.name" :message="errors.name" />
              </div>

              <!-- Phone Number -->
              <div class="space-y-2">
                <Label for="phone_number">Phone Number *</Label>
                <Input
                  id="phone_number"
                  v-model="form.phone_number"
                  type="tel"
                  placeholder="Enter phone number"
                  :className="{ 'border-red-500': errors.phone_number }"
                />
                <InputError v-if="errors.phone_number" :message="errors.phone_number" />
              </div>

              <!-- Email -->
              <div class="space-y-2 md:col-span-2">
                <Label for="email">Email Address *</Label>
                <Input
                  id="email"
                  v-model="form.email"
                  type="email"
                  placeholder="Enter email address"
                  :className="{ 'border-red-500': errors.email }"
                />
                <InputError v-if="errors.email" :message="errors.email" />
              </div>

              <!-- Address Line 1 -->
              <div class="space-y-2 md:col-span-2">
                <Label for="address_line_1">Address Line 1 *</Label>
                <Input
                  id="address_line_1"
                  v-model="form.address_line_1"
                  type="text"
                  placeholder="Enter address line 1"
                  :className="{ 'border-red-500': errors.address_line_1 }"
                />
                <InputError v-if="errors.address_line_1" :message="errors.address_line_1" />
              </div>

              <!-- Address Line 2 -->
              <div class="space-y-2 md:col-span-2">
                <Label for="address_line_2">Address Line 2</Label>
                <Input
                  id="address_line_2"
                  v-model="form.address_line_2"
                  type="text"
                  placeholder="Enter address line 2 (optional)"
                />
                <InputError v-if="errors.address_line_2" :message="errors.address_line_2" />
              </div>

              <!-- Country -->
              <div class="space-y-2">
                <Label for="country">Country *</Label>
                <select
                  id="country"
                  v-model="form.country"
                  className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :className="{ 'border-red-500': errors.country }"
                  @change="onCountryChange"
                >
                  <option value="">Select Country</option>
                  <option v-for="country in countries" :key="country.code" :value="country.name">
                    {{ country.name }}
                  </option>
                </select>
                <InputError v-if="errors.country" :message="errors.country" />
              </div>

              <!-- State -->
              <div class="space-y-2">
                <Label for="state">State *</Label>
                <select
                  id="state"
                  v-model="form.state"
                  className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :className="{ 'border-red-500': errors.state }"
                  @change="onStateChange"
                  :disabled="!form.country"
                >
                  <option value="">Select State</option>
                  <option v-for="state in availableStates" :key="state.code" :value="state.name">
                    {{ state.name }}
                  </option>
                </select>
                <InputError v-if="errors.state" :message="errors.state" />
              </div>

              <!-- City -->
              <div class="space-y-2">
                <Label for="city">City *</Label>
                <select
                  id="city"
                  v-model="form.city"
                  className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :className="{ 'border-red-500': errors.city }"
                  :disabled="!form.state"
                >
                  <option value="">Select City</option>
                  <option v-for="city in availableCities" :key="city.code" :value="city.name">
                    {{ city.name }}
                  </option>
                </select>
                <InputError v-if="errors.city" :message="errors.city" />
              </div>
            </div>

            <div class="flex justify-end space-x-3 pt-6">
              <Button type="button" variant="outline" @click="$inertia.visit(route('customers.index'))">
                Cancel
              </Button>
              <Button type="submit" :disabled="isSubmitting">
                <Icon v-if="isSubmitting" name="loader-2" class="mr-2 h-4 w-4 animate-spin" />
                {{ isEditing ? 'Update Customer' : 'Create Customer' }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import Button from '@/components/ui/button/Button.vue'
import Card from '@/components/ui/card/Card.vue'
import CardContent from '@/components/ui/card/CardContent.vue'
import CardDescription from '@/components/ui/card/CardDescription.vue'
import CardHeader from '@/components/ui/card/CardHeader.vue'
import CardTitle from '@/components/ui/card/CardTitle.vue'
import Icon from '@/components/Icon.vue'
import Input from '@/components/ui/input/Input.vue'
import InputError from '@/components/InputError.vue'
import Label from '@/components/ui/label/Label.vue'
import Link from '@/components/TextLink.vue'

interface Customer {
  id: number
  name: string
  phone_number: string
  email: string
  address_line_1: string
  address_line_2?: string
  country: string
  state: string
  city: string
}

interface Props {
  customer?: Customer
  isEditing?: boolean
  errors?: Record<string, string>
}

const props = withDefaults(defineProps<Props>(), {
  isEditing: false,
  errors: () => ({})
})

// Sample data for countries, states, and cities
const countries = [
  { code: 'US', name: 'United States' },
  { code: 'CA', name: 'Canada' },
  { code: 'GB', name: 'United Kingdom' },
  { code: 'AU', name: 'Australia' },
  { code: 'DE', name: 'Germany' },
  { code: 'FR', name: 'France' },
  { code: 'JP', name: 'Japan' },
  { code: 'IN', name: 'India' },
  { code: 'BR', name: 'Brazil' },
  { code: 'MX', name: 'Mexico' }
]

const statesData = {
  'United States': [
    { code: 'CA', name: 'California' },
    { code: 'NY', name: 'New York' },
    { code: 'TX', name: 'Texas' },
    { code: 'FL', name: 'Florida' },
    { code: 'IL', name: 'Illinois' }
  ],
  'Canada': [
    { code: 'ON', name: 'Ontario' },
    { code: 'QC', name: 'Quebec' },
    { code: 'BC', name: 'British Columbia' },
    { code: 'AB', name: 'Alberta' },
    { code: 'NS', name: 'Nova Scotia' }
  ],
  'United Kingdom': [
    { code: 'ENG', name: 'England' },
    { code: 'SCT', name: 'Scotland' },
    { code: 'WLS', name: 'Wales' },
    { code: 'NIR', name: 'Northern Ireland' }
  ],
  'Australia': [
    { code: 'NSW', name: 'New South Wales' },
    { code: 'VIC', name: 'Victoria' },
    { code: 'QLD', name: 'Queensland' },
    { code: 'WA', name: 'Western Australia' },
    { code: 'SA', name: 'South Australia' }
  ]
}

const citiesData = {
  'California': [
    { code: 'LA', name: 'Los Angeles' },
    { code: 'SF', name: 'San Francisco' },
    { code: 'SD', name: 'San Diego' },
    { code: 'SJ', name: 'San Jose' }
  ],
  'New York': [
    { code: 'NYC', name: 'New York City' },
    { code: 'BUF', name: 'Buffalo' },
    { code: 'ROC', name: 'Rochester' },
    { code: 'SYR', name: 'Syracuse' }
  ],
  'Ontario': [
    { code: 'TOR', name: 'Toronto' },
    { code: 'OTT', name: 'Ottawa' },
    { code: 'HAM', name: 'Hamilton' },
    { code: 'LON', name: 'London' }
  ],
  'Quebec': [
    { code: 'MON', name: 'Montreal' },
    { code: 'QUE', name: 'Quebec City' },
    { code: 'LAV', name: 'Laval' },
    { code: 'GAT', name: 'Gatineau' }
  ]
}

const form = useForm({
  name: props.customer?.name || '',
  phone_number: props.customer?.phone_number || '',
  email: props.customer?.email || '',
  address_line_1: props.customer?.address_line_1 || '',
  address_line_2: props.customer?.address_line_2 || '',
  country: props.customer?.country || '',
  state: props.customer?.state || '',
  city: props.customer?.city || ''
})

const isSubmitting = ref(false)

const availableStates = computed(() => {
  if (!form.country) return []
  return statesData[form.country as keyof typeof statesData] || []
})

const availableCities = computed(() => {
  if (!form.state) return []
  return citiesData[form.state as keyof typeof citiesData] || []
})

const onCountryChange = () => {
  form.state = ''
  form.city = ''
}

const onStateChange = () => {
  form.city = ''
}

const submitForm = () => {
  isSubmitting.value = true
  
  if (props.isEditing) {
    form.put(route('customers.update', props.customer?.id), {
      onFinish: () => {
        isSubmitting.value = false
      }
    })
  } else {
    form.post(route('customers.store'), {
      onFinish: () => {
        isSubmitting.value = false
      }
    })
  }
}

// Watch for prop changes to update form
watch(() => props.customer, (newCustomer) => {
  if (newCustomer) {
    form.name = newCustomer.name
    form.phone_number = newCustomer.phone_number
    form.email = newCustomer.email
    form.address_line_1 = newCustomer.address_line_1
    form.address_line_2 = newCustomer.address_line_2 || ''
    form.country = newCustomer.country
    form.state = newCustomer.state
    form.city = newCustomer.city
  }
}, { immediate: true })
</script>
