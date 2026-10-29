export const validUser = {
  name: "Michael Owen",
  password: "michael123",
  createEmail: () => `owen${Date.now()}@test.com`,
};

export const invalidUser = [
  {
    title: "empty email",
    name: "Michael Owen",
    email: "",
    password: "michael123",
    confirm: "michael123",
  },
  {
    title: "password not same",
    name: "Michael Owen",
    email: "michael@test.com",
    password: "michael123",
    confirm: "owen123",
  },
];
