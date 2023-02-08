<script setup lang="ts"></script>

<template>
    <VLayout>
        <VAppBar title="Renbot"></VAppBar>

        <VNavigationDrawer>
            <VList>
                <VListItem title="Commands"></VListItem>
            </VList>
        </VNavigationDrawer>

        <VMain>
            <slot></slot>
        </VMain>
    </VLayout>
</template>
