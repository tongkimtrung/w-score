<template>
  <div class="form-list flex-grow-1 d-flex">
    <div class="d-flex flex-column flex-grow-1">
      <div class="d-flex flex-row title-box">
        <div class="list-title flex-grow-1 text-start">Danh sách sản phẩm</div>
      </div>
      <div class="d-flex flex-row toolbar-box justify-content-between">
        <div class="left-toolbar d-flex flex-row">
          <div class="m-search_form flex-row d-flex align-items-center d-flex">
            <InputText type="search" v-model="value" class="ms-input_search w-100" placeholder="Tìm kiếm"/>
            <div class="icon24 icon search-right search"></div>
          </div>
        </div>
        <div class="right-toolbar d-flex flex-row">
          <Button

              class="ms-btn blue d-flex justify-content-center flex-grow-1 ms-btn_search ps-3 pe-3 gap-2">
            <div class="icon-only icon-simple_cart"></div>
            <div class="fw-semibold">Thêm sản phẩm</div>
          </Button>
        </div>
      </div>
      <div class="box list-content flex-grow-1 flex-row">
        <DataTable paginator :rows="10" :rowsPerPageOptions="[5, 10, 20, 50]" class="flex1 flex-column"
                   :class="{ 'loading': isLoading }" :loading="isLoading"
                   :value="isLoading ? Array.from({ length: 8 }, () => ({ ...this.department })) : data"
                   currentPageReportTemplate="{first} to {last} of {totalRecords}"
                   paginatorTemplate="FirstPageLink PrevPageLink flex1 CurrentPageReport NextPageLink LastPageLink RowsPerPageDropdown"
                   @rowDblclick="onRowSelect($event.data)" tableStyle="min-width: 100%" rowHover>
          <template #paginatorstart>
            <Button type="button" icon="pi pi-refresh" text/>
          </template>
          <template #paginatorend>
            bản ghi/trang
          </template>
          <template #empty>
            <div>
              Không có dữ liệu
            </div>
          </template>
          <Column header="STT" style="width: 100px;" class="text-center">
            <template #body="slotProps">
              <div v-if="!isLoading"> {{ slotProps.index + 1 }}</div>
              <div v-else>
                <Skeleton height="18px" class="mb-2"></Skeleton>
              </div>
            </template>
          </Column>
          <Column field="DepartmentCode" style="width: 30vw;" header="Mã phòng thi">
            <template #body="{ data, field, slotProps }">
              <div v-if="!isLoading"> {{ data[field] }}</div>
              <div v-else>
                <Skeleton height="18px" class="mb-2"></Skeleton>
              </div>
            </template>
          </Column>
          <Column field="DepartmentName" dataKey="id" header="Tên phòng thi">
            <template #body="{ data, field, slotProps }">
              <div v-if="!isLoading"> {{ data[field] }}</div>
              <div v-else>
                <Skeleton height="18px" class="mb-2"></Skeleton>
              </div>
            </template>
          </Column>
          <Column dataKey="id" header="Trạng thái" style="width: 200px;">
            <template #body="{ data, field, slotProps }">
              <div v-if="!isLoading">
                <div class="d-flex status-ctn max-content" v-if="data['is_exist']"
                     style="background-color: rgb(229, 250, 237);">
                  <div class="status-dot" style="background-color: rgb(0, 200, 83);"></div>
                  <div class="status-text" style="color: rgb(0, 200, 83);">Đang sử dụng</div>
                </div>
                <div class="d-flex status-ctn max-content" v-else
                     style="background-color: rgb(254, 243, 231);">
                  <div class="status-dot" style="background-color: rgb(243, 141, 21);"></div>
                  <div class="status-text" style="color: rgb(243, 141, 21);">Không sử dụng</div>
                </div>
              </div>
              <div v-else>
                <Skeleton height="18px" class="mb-2"></Skeleton>
              </div>
            </template>
          </Column>
          <Column alignFrozen="right" style="width: 100px; text-align: center;" frozen header="Thao tác">
            <template #body="slotProps">
              <div class="row-actions flex-row" v-if="!isLoading">
                <div class="item" @click="onRowSelect(slotProps.data)">
                  <div class="v-popover popover">
                    <div class="trigger">
                      <div class="icon24 edit"></div>
                    </div>
                  </div>
                </div>
                <div class="item" @click="deleteRowSelect(slotProps.data)">
                  <div class="v-popover popover">
                    <div class="icon24 delete"></div>
                  </div>
                </div>
              </div>
              <div v-else>
                <Skeleton height="18px" class="mb-2"></Skeleton>
              </div>
            </template>
          </Column>

        </DataTable>
      </div>
    </div>
  </div>
</template>

<script>
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Skeleton from 'primevue/skeleton';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

export default {
  components: {
    Button,
    InputText,
    Skeleton,
    DataTable,
    Column
  }
}
</script>

<style lang="scss">


</style>
