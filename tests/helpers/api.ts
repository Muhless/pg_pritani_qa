import { APIRequestContext, expect } from "@playwright/test";

export async function createCustomer(
  request: APIRequestContext,
  data: { name: string; phone?: string; address?: string },
) {
  const res = await request.post("/_test/customers", { form: data });
  if (!res.ok()) {
    console.log(res.status(), await res.text());
  }
  expect(res.ok()).toBeTruthy();
  return res.json();
}

export async function createItemWithStock(
  request: APIRequestContext,
  data: {
    name: string;
    category?: "grain" | "rice" | "bran" | "husk" | "broken_rice";
    quantity_kg?: number;
  },
) {
  const res = await request.post("/_test/item-types", {
    form: {
      name: data.name,
      category: data.category ?? "rice",
      quantity_kg: String(data.quantity_kg ?? 1000),
    },
    headers: { Accept: "application/json" },
  });
  if (!res.ok()) {
    console.log(res.status(), await res.text());
  }
  expect(res.ok()).toBeTruthy();
  return res.json();
}
